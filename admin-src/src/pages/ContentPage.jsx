import { useState, useEffect } from 'react'
import { useParams } from 'react-router-dom'
import { contentApi, uploadFile } from '../services/api'

// Image path resolver — handles Vite dev proxy vs built /admin/ production path
const _isDev = (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') &&
               (window.location.port === '5173' || window.location.port === '5174')
function imgSrc(path) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return _isDev ? `/tumsdachurch.org/${path}` : `../${path}`
}

const fieldsConfig = {
  departments: ['name', 'description', 'scripture_quote', 'scripture_reference', 'external_link', 'sort_order', 'logo_path', 'chair_name', 'chair_photo_path'],
  ministries: ['name', 'description', 'scripture_quote', 'scripture_reference', 'sort_order', 'logo_path', 'chair_name', 'chair_photo_path'],
  leadership: ['name', 'position', 'photo_path', 'statement', 'sort_order'],
  sermons: ['title', 'youtube_url', 'description', 'is_featured', 'published_at'],
  events: ['title', 'event_date', 'facilitator', 'description'],
  weekly_meetings: ['day_of_week', 'time_range', 'program_name', 'sort_order'],
  resources: ['title', 'description', 'icon_path', 'link_url', 'category', 'sort_order'],
  missions: ['title', 'theme_text', 'theme_verse', 'theme_song', 'start_date', 'end_date', 'description', 'is_upcoming', 'sort_order', 'logo_path', 'chair_name', 'chair_photo_path'],
  announcements: ['title', 'content', 'sort_order'],
  word_of_the_day: ['content', 'reference'],
}

// ── Image Upload Field Component ─────────────────────────────────────────────
function ImageUploadField({ label, value, onChange }) {
  const [uploading, setUploading] = useState(false)
  const [uploadError, setUploadError] = useState('')

  const previewSrc = imgSrc(value)

  const handleFileChange = async (e) => {
    const file = e.target.files[0]
    if (!file) return
    setUploading(true)
    setUploadError('')
    try {
      const result = await uploadFile(file)
      onChange(result.url)
    } catch (err) {
      setUploadError((err && err.error) ? err.error : 'Upload failed. Please try again.')
    } finally {
      setUploading(false)
      e.target.value = ''
    }
  }

  return (
    <div className="flex flex-col gap-2">
      <label className="text-sm font-semibold text-slate-700" style={{ fontFamily: "'Inter', sans-serif" }}>{label}</label>
      {previewSrc && (
        <div className="flex items-center gap-3 p-2 bg-slate-50 rounded-xl border border-slate-200">
          <img
            src={previewSrc}
            alt="Preview"
            className="w-14 h-14 rounded-xl object-cover border border-slate-200 shadow-sm"
            onError={e => { e.target.style.display = 'none' }}
          />
          <div className="flex-1 min-w-0">
            <p className="text-xs text-slate-500 truncate">{value}</p>
            <button type="button" onClick={() => onChange('')} className="text-xs text-red-500 hover:text-red-700 font-semibold mt-1">✕ Remove</button>
          </div>
        </div>
      )}
      <label className={`flex items-center gap-2 px-4 py-3 rounded-xl border-2 border-dashed cursor-pointer transition-all ${
        uploading ? 'border-brand/40 bg-brand/5 opacity-60' : 'border-slate-200 hover:border-brand/50 hover:bg-blue-50 bg-slate-50'
      }`}>
        <svg className="w-5 h-5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span className="text-sm font-medium text-slate-500" style={{ fontFamily: "'Inter', sans-serif" }}>
          {uploading ? 'Uploading…' : 'Click to upload image'}
        </span>
        <input type="file" accept="image/*" className="hidden" onChange={handleFileChange} disabled={uploading} />
      </label>
      {uploadError && <p className="text-xs text-red-500 font-medium">{uploadError}</p>}
      <input
        type="text"
        className="w-full px-4 py-2 rounded-xl border border-slate-200 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition-all text-slate-600 text-xs bg-slate-50 focus:bg-white"
        value={value || ''}
        onChange={e => onChange(e.target.value)}
        placeholder="Or paste image URL / relative path…"
        style={{ fontFamily: "'Inter', sans-serif" }}
      />
    </div>
  )
}

export default function ContentPage() {
  const { type } = useParams()
  const [items, setItems] = useState([])
  const [editing, setEditing] = useState(null)
  const [formData, setFormData] = useState({})
  const [error, setError] = useState('')
  const [success, setSuccess] = useState('')

  useEffect(() => {
    loadItems()
    cancelEdit()
  }, [type])

  async function loadItems() {
    try {
      const data = await contentApi.list(type)
      setItems(Array.isArray(data) ? data : [])
    } catch (err) {
      setError(err.message || 'Failed to load content.')
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setSuccess('')
    try {
      if (editing) {
        await contentApi.update(type, editing.id, formData)
        setSuccess('Item updated successfully.')
      } else {
        await contentApi.create(type, formData)
        setSuccess('Item created successfully.')
      }
      cancelEdit()
      loadItems()
    } catch (err) {
      setError(err.message || 'An error occurred while saving.')
    }
  }

  const handleDelete = async (id) => {
    if (window.confirm('Are you sure you want to delete this item?')) {
      setError('')
      setSuccess('')
      try {
        await contentApi.delete(type, id)
        setSuccess('Item deleted successfully.')
        loadItems()
      } catch (err) {
        setError(err.message || 'Failed to delete item.')
      }
    }
  }

  const startEdit = (item) => {
    setEditing(item)
    // Create clean form data with only valid fields from the config
    const cleaned = {}
    fieldsConfig[type].forEach(f => {
      cleaned[f] = item[f] === null ? '' : item[f]
    })
    setFormData(cleaned)
  }

  const cancelEdit = () => {
    setEditing(null)
    const initial = {}
    if (fieldsConfig[type]) {
      fieldsConfig[type].forEach(f => {
        if (f === 'sort_order') initial[f] = 0
        else if (f === 'is_featured' || f === 'is_upcoming') initial[f] = 0
        else if (f === 'day_of_week') initial[f] = 'Sunday'
        else initial[f] = ''
      })
    }
    setFormData(initial)
  }

  const getHeaders = () => {
    switch (type) {
      case 'sermons': return ['Title', 'Publish Date', 'Featured']
      case 'events': return ['Title', 'Date', 'Facilitator']
      case 'weekly_meetings': return ['Program Name', 'Day', 'Time']
      case 'missions': return ['Title', 'Theme', 'Dates', 'Upcoming']
      case 'announcements': return ['Title', 'Content Preview', 'Order']
      case 'word_of_the_day': return ['Verse Reference', 'Content Preview']
      case 'leadership': return ['Photo', 'Name', 'Position', 'Order']
      case 'departments':
      case 'ministries': return ['Logo', 'Name', 'Scripture', 'Order']
      default: return ['Title/Name', 'Details']
    }
  }

  const renderCells = (item) => {
    switch (type) {
      case 'sermons':
        return (
          <>
            <td className="px-6 py-4 font-semibold text-slate-800" style={{ fontFamily: "'Inter', sans-serif" }}>{item.title}</td>
            <td className="px-6 py-4 text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>{item.published_at || '-'}</td>
            <td className="px-6 py-4"><span className={`px-3 py-1 rounded-full text-xs font-bold ${item.is_featured ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600'}`}>{item.is_featured ? 'Yes' : 'No'}</span></td>
          </>
        )
      case 'events':
        return (
          <>
            <td className="px-6 py-4 font-semibold text-slate-800" style={{ fontFamily: "'Inter', sans-serif" }}>{item.title}</td>
            <td className="px-6 py-4 text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>{item.event_date || '-'}</td>
            <td className="px-6 py-4 text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>{item.facilitator || '-'}</td>
          </>
        )
      case 'weekly_meetings':
        return (
          <>
            <td className="px-6 py-4 font-semibold text-slate-800" style={{ fontFamily: "'Inter', sans-serif" }}>{item.program_name}</td>
            <td className="px-6 py-4 text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>{item.day_of_week}</td>
            <td className="px-6 py-4 text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>{item.time_range}</td>
          </>
        )
      case 'missions':
        return (
          <>
            <td className="px-6 py-4 font-semibold text-slate-800" style={{ fontFamily: "'Inter', sans-serif" }}>{item.title}</td>
            <td className="px-6 py-4 text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>{item.theme_text} ({item.theme_verse})</td>
            <td className="px-6 py-4 text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>{item.start_date} to {item.end_date}</td>
            <td className="px-6 py-4"><span className={`px-3 py-1 rounded-full text-xs font-bold ${item.is_upcoming ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'}`}>{item.is_upcoming ? 'Yes' : 'No'}</span></td>
          </>
        )
      case 'announcements':
        return (
          <>
            <td className="px-6 py-4 font-semibold text-slate-800" style={{ fontFamily: "'Inter', sans-serif" }}>{item.title}</td>
            <td className="px-6 py-4 text-slate-600 text-sm max-w-xs" style={{ fontFamily: "'Inter', sans-serif" }}>{item.content?.slice(0, 80)}{item.content?.length > 80 ? '…' : ''}</td>
            <td className="px-6 py-4 text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>{item.sort_order}</td>
          </>
        )
      case 'word_of_the_day':
        return (
          <>
            <td className="px-6 py-4 font-semibold text-slate-800" style={{ fontFamily: "'Inter', sans-serif" }}>{item.reference}</td>
            <td className="px-6 py-4 text-slate-600 text-sm max-w-xs" style={{ fontFamily: "'Inter', sans-serif" }}>{item.content?.slice(0, 80)}{item.content?.length > 80 ? '…' : ''}</td>
          </>
        )
      case 'leadership':
        return (
          <>
            <td className="px-4 py-3">
              {item.photo_path
                ? <img src={imgSrc(item.photo_path)} alt={item.name} className="w-10 h-10 rounded-full object-cover border-2 border-slate-200 shadow-sm" onError={e => { e.target.style.display = 'none' }} />
                : <div className="w-10 h-10 rounded-full bg-gradient-to-br from-brand/20 to-slate-200 flex items-center justify-center text-brand font-bold text-sm">{item.name?.[0] || '?'}</div>
              }
            </td>
            <td className="px-6 py-4 font-semibold text-slate-800" style={{ fontFamily: "'Inter', sans-serif" }}>{item.name}</td>
            <td className="px-6 py-4 text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>{item.position}</td>
            <td className="px-6 py-4 text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>{item.sort_order}</td>
          </>
        )
      case 'departments':
      case 'ministries':
        return (
          <>
            <td className="px-4 py-3">
              {item.logo_path
                ? <img src={imgSrc(item.logo_path)} alt={item.name} className="w-10 h-10 rounded-lg object-cover border border-slate-200 shadow-sm" onError={e => { e.target.style.display = 'none' }} />
                : <div className="w-10 h-10 rounded-lg bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-400 text-xl">🏛</div>
              }
            </td>
            <td className="px-6 py-4 font-semibold text-slate-800" style={{ fontFamily: "'Inter', sans-serif" }}>{item.name}</td>
            <td className="px-6 py-4 text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>{item.scripture_reference || '-'}</td>
            <td className="px-6 py-4 text-slate-600" style={{ fontFamily: "'Inter', sans-serif" }}>{item.sort_order}</td>
          </>
        )
      default:
        return (
          <>
            <td className="px-6 py-4 font-semibold text-slate-800" style={{ fontFamily: "'Inter', sans-serif" }}>{item.name || item.title || item.program_name || item.position}</td>
            <td className="px-6 py-4 text-slate-600 text-sm max-w-xs truncate" style={{ fontFamily: "'Inter', sans-serif" }}>{item.description || item.statement || '-'}</td>
          </>
        )
    }
  }

  const renderInputField = (key) => {
    const label = key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())

    if (key === 'photo_path' || key === 'logo_path' || key === 'chair_photo_path' || key === 'icon_path') {
      return (
        <ImageUploadField
          key={key}
          label={label}
          value={formData[key] || ''}
          onChange={val => setFormData({ ...formData, [key]: val })}
        />
      )
    }

    if (key === 'description' || key === 'statement' || key === 'scripture_quote' || key === 'content') {
      return (
        <div key={key} className="flex flex-col gap-2">
          <label className="text-sm font-semibold text-slate-700" style={{ fontFamily: "'Inter', sans-serif" }}>{label}</label>
          <textarea
            className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition-all text-slate-800 placeholder-slate-400 font-medium bg-slate-50 focus:bg-white"
            rows={3}
            value={formData[key] || ''}
            onChange={e => setFormData({ ...formData, [key]: e.target.value })}
            placeholder={`Enter ${label.toLowerCase()}`}
            style={{ fontFamily: "'Inter', sans-serif" }}
          />
        </div>
      )
    }

    if (key === 'is_featured' || key === 'is_upcoming') {
      const isChecked = formData[key] === 1 || formData[key] === true
      return (
        <div key={key} className="flex items-center gap-3 py-2">
          <input
            type="checkbox"
            id={`checkbox-${key}`}
            className="w-5 h-5 rounded text-brand border-slate-300 focus:ring-brand transition-all cursor-pointer bg-slate-50"
            checked={isChecked}
            onChange={e => setFormData({ ...formData, [key]: e.target.checked ? 1 : 0 })}
          />
          <label htmlFor={`checkbox-${key}`} className="text-sm font-semibold text-slate-700 cursor-pointer select-none" style={{ fontFamily: "'Inter', sans-serif" }}>{label}</label>
        </div>
      )
    }

    if (key.includes('date') || key === 'published_at') {
      return (
        <div key={key} className="flex flex-col gap-2">
          <label className="text-sm font-semibold text-slate-700" style={{ fontFamily: "'Inter', sans-serif" }}>{label}</label>
          <input
            type="date"
            className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition-all text-slate-800 font-medium bg-slate-50 focus:bg-white"
            value={formData[key] || ''}
            onChange={e => setFormData({ ...formData, [key]: e.target.value })}
            style={{ fontFamily: "'Inter', sans-serif" }}
          />
        </div>
      )
    }

    if (key === 'sort_order') {
      return (
        <div key={key} className="flex flex-col gap-2">
          <label className="text-sm font-semibold text-slate-700" style={{ fontFamily: "'Inter', sans-serif" }}>{label}</label>
          <input
            type="number"
            className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition-all text-slate-800 font-medium bg-slate-50 focus:bg-white"
            value={formData[key] === undefined ? 0 : formData[key]}
            onChange={e => setFormData({ ...formData, [key]: parseInt(e.target.value) || 0 })}
            style={{ fontFamily: "'Inter', sans-serif" }}
          />
        </div>
      )
    }

    if (key === 'day_of_week') {
      const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
      return (
        <div key={key} className="flex flex-col gap-2">
          <label className="text-sm font-semibold text-slate-700" style={{ fontFamily: "'Inter', sans-serif" }}>{label}</label>
          <select
            className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition-all text-slate-800 bg-slate-50 font-medium focus:bg-white"
            value={formData[key] || 'Sunday'}
            onChange={e => setFormData({ ...formData, [key]: e.target.value })}
            style={{ fontFamily: "'Inter', sans-serif" }}
          >
            {days.map(d => <option key={d} value={d}>{d}</option>)}
          </select>
        </div>
      )
    }

    return (
      <div key={key} className="flex flex-col gap-2">
        <label className="text-sm font-semibold text-slate-700" style={{ fontFamily: "'Inter', sans-serif" }}>{label}</label>
        <input
          type="text"
          className="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none transition-all text-slate-800 placeholder-slate-400 font-medium bg-slate-50 focus:bg-white"
          value={formData[key] || ''}
          onChange={e => setFormData({ ...formData, [key]: e.target.value })}
          placeholder={`Enter ${label.toLowerCase()}`}
          style={{ fontFamily: "'Inter', sans-serif" }}
        />
      </div>
    )
  }

  const pageTitles = {
    departments: 'Departments',
    ministries: 'Ministries',
    leadership: 'Leadership',
    sermons: 'Sermons',
    events: 'Events Calendar',
    weekly_meetings: 'Weekly Meetings',
    resources: 'Resources',
    missions: 'Missions',
    announcements: 'Church Notice Board',
    word_of_the_day: 'Word of the Day',
  }
  const pageTitle = pageTitles[type] || type.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
  const fields = fieldsConfig[type] || []

  return (
    <div className="space-y-8">
      <div className="flex justify-between items-center">
        <h2 className="text-3xl font-extrabold text-slate-900 tracking-tight" style={{ fontFamily: "'League Spartan', sans-serif" }}>
          {pageTitle}
        </h2>
      </div>

      {error && (
        <div className="p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl text-sm font-medium" style={{ fontFamily: "'Inter', sans-serif" }}>
          {error}
        </div>
      )}

      {success && (
        <div className="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl text-sm font-medium" style={{ fontFamily: "'Inter', sans-serif" }}>
          {success}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {/* Form Card */}
        <div className="lg:col-span-1 bg-white rounded-2xl shadow-xl border border-slate-100 p-6 h-fit">
          <h3 className="text-xl font-bold text-slate-900 mb-6 border-b border-slate-100 pb-3" style={{ fontFamily: "'League Spartan', sans-serif" }}>
            {editing ? 'Edit Item' : 'Add New Item'}
          </h3>
          <form onSubmit={handleSubmit} className="space-y-5">
            {fields.map(key => renderInputField(key))}
            <div className="flex gap-3 pt-2">
              <button
                type="submit"
                className="flex-1 py-3 px-4 bg-brand hover:bg-brand-dark text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5"
                style={{ fontFamily: "'Inter', sans-serif" }}
              >
                {editing ? 'Save Changes' : 'Create Item'}
              </button>
              {editing && (
                <button
                  type="button"
                  className="py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-all duration-200"
                  style={{ fontFamily: "'Inter', sans-serif" }}
                  onClick={cancelEdit}
                >
                  Cancel
                </button>
              )}
            </div>
          </form>
        </div>

        {/* Table / List Card */}
        <div className="lg:col-span-2 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="bg-slate-900 text-white border-b border-slate-800">
                  <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>ID</th>
                  {getHeaders().map(h => (
                    <th key={h} className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>{h}</th>
                  ))}
                  <th className="px-6 py-4 text-left font-bold text-sm tracking-wider" style={{ fontFamily: "'League Spartan', sans-serif" }}>Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-slate-100">
                {items.length === 0 ? (
                  <tr>
                    <td colSpan={getHeaders().length + 2} className="px-6 py-8 text-center text-slate-400" style={{ fontFamily: "'Inter', sans-serif" }}>
                      No items found. Create one using the form on the left.
                    </td>
                  </tr>
                ) : (
                  items.map(item => (
                    <tr key={item.id} className="hover:bg-slate-50 transition-colors">
                      <td className="px-6 py-4 text-slate-500 font-semibold text-sm" style={{ fontFamily: "'Inter', sans-serif" }}>{item.id}</td>
                      {renderCells(item)}
                      <td className="px-6 py-4">
                        <div className="flex gap-2">
                          <button
                            className="px-3 py-1.5 bg-slate-100 hover:bg-brand hover:text-white text-slate-700 text-xs font-bold rounded-lg transition-all duration-200"
                            style={{ fontFamily: "'Inter', sans-serif" }}
                            onClick={() => startEdit(item)}
                          >
                            Edit
                          </button>
                          <button
                            className="px-3 py-1.5 bg-slate-100 hover:bg-red-600 hover:text-white text-slate-700 text-xs font-bold rounded-lg transition-all duration-200"
                            style={{ fontFamily: "'Inter', sans-serif" }}
                            onClick={() => handleDelete(item.id)}
                          >
                            Delete
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  )
}
