document.addEventListener('DOMContentLoaded', () => {
	const carousels = document.querySelectorAll('.carousel');
	carousels.forEach(c => {
		const interval = c.getAttribute('data-bs-interval') || 5000;
		const carousel = new bootstrap.Carousel(c, { interval: Number(interval) });
	});

	// Mobile Side Panel Functionality
	const mobileMenuToggle = document.getElementById('mobileMenuToggle');
	const mobileSidePanel = document.getElementById('mobileSidePanel');
	const sidePanelClose = document.getElementById('sidePanelClose');
	const sidePanelOverlay = document.getElementById('sidePanelOverlay');
	const body = document.body;

	// Open side panel
	if (mobileMenuToggle) {
		mobileMenuToggle.addEventListener('click', () => {
			mobileSidePanel.classList.add('active');
			body.classList.add('side-panel-open');
		});
	}

	// Close side panel
	const closeSidePanel = () => {
		mobileSidePanel.classList.remove('active');
		body.classList.remove('side-panel-open');
	};

	if (sidePanelClose) {
		sidePanelClose.addEventListener('click', closeSidePanel);
	}

	if (sidePanelOverlay) {
		sidePanelOverlay.addEventListener('click', closeSidePanel);
	}

	// Close side panel when clicking on menu links
	const sidePanelLinks = document.querySelectorAll('.side-panel-link');
	sidePanelLinks.forEach(link => {
		link.addEventListener('click', closeSidePanel);
	});

	// Close side panel on escape key
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && mobileSidePanel.classList.contains('active')) {
			closeSidePanel();
		}
	});

	// Seven Section Layout Management
	const maintainSevenSectionLayout = () => {
		const seventhSection = document.querySelector('.seventh-section');
		const contentWrapper = document.querySelector('.content-wrapper');
		
		if (seventhSection && contentWrapper) {
			const viewportWidth = window.innerWidth;
			const seventhSectionWidth = viewportWidth / 7;
			const contentWrapperWidth = (viewportWidth * 6) / 7;
			
			seventhSection.style.width = `${seventhSectionWidth}px`;
			contentWrapper.style.width = `${contentWrapperWidth}px`;
			
			seventhSection.style.right = '0';
			seventhSection.style.left = 'auto';
			
			contentWrapper.style.float = 'left';
			contentWrapper.style.clear = 'none';
			
		}
	};
	
	

	maintainSevenSectionLayout();
	
	window.addEventListener('resize', maintainSevenSectionLayout);
	window.addEventListener('orientationchange', maintainSevenSectionLayout);
	
	// Seventh section scroll effect
	const initSeventhSectionScrollEffect = () => {
		const seventhSection = document.querySelector('.seventh-section');
		const logoWhite = document.querySelector('.logo-white');
		const logoBlack = document.querySelector('.logo-black');
		
		if (!seventhSection) return;
		
		const updateSeventhSectionColor = () => {
			const scrollY = window.scrollY;
			const windowHeight = window.innerHeight;
			const documentHeight = document.documentElement.scrollHeight;
			const scrollProgress = Math.min(scrollY / (documentHeight - windowHeight), 1);
			const red = Math.round(0 + (255 - 0) * scrollProgress);
			const green = Math.round(102 + (255 - 102) * scrollProgress);
			const blue = Math.round(204 + (255 - 204) * scrollProgress);
			seventhSection.style.backgroundColor = `rgb(${red}, ${green}, ${blue})`;
			if (logoWhite && logoBlack) {
				logoWhite.style.opacity = 1 - scrollProgress;
				logoBlack.style.opacity = scrollProgress;
			}
		};
		
		window.addEventListener('scroll', updateSeventhSectionColor);
		
		updateSeventhSectionColor();
	};

	initSeventhSectionScrollEffect();

	// Popup Card Functionality
	const initPopupCards = () => {
		const supportButtons = document.querySelectorAll('.support-btn');
		const supportPopup = document.getElementById('supportPopup');
		const supportClose = document.getElementById('supportClose');

		supportButtons.forEach(button => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				if (supportPopup) {
					supportPopup.classList.add('active');
					document.body.style.overflow = 'hidden';
				}
			});
		});

		if (supportClose) {
			supportClose.addEventListener('click', () => {
				supportPopup.classList.remove('active');
				document.body.style.overflow = '';
			});
		}

		// Mission Chair popup functionality
		const missionChairButtons = document.querySelectorAll('.mission-chair-btn');
		const missionChairPopup = document.getElementById('missionChairPopup');
		const missionChairClose = document.getElementById('missionChairClose');

		missionChairButtons.forEach(button => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				if (missionChairPopup) {
					missionChairPopup.classList.add('active');
					document.body.style.overflow = 'hidden';
				}
			});
		});

		if (missionChairClose) {
			missionChairClose.addEventListener('click', () => {
				missionChairPopup.classList.remove('active');
				document.body.style.overflow = '';
			});
		}

		// Gallery popup functionality
		const galleryButtons = document.querySelectorAll('.gallery-btn');
		const galleryPopup = document.getElementById('galleryPopup');
		const galleryClose = document.getElementById('galleryClose');

		galleryButtons.forEach(button => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				if (galleryPopup) {
					galleryPopup.classList.add('active');
					document.body.style.overflow = 'hidden';
				}
			});
		});

		if (galleryClose) {
			galleryClose.addEventListener('click', () => {
				galleryPopup.classList.remove('active');
				document.body.style.overflow = '';
			});
		}

		// Contact popup functionality
		const contactButtons = document.querySelectorAll('.contact-btn');
		const contactPopup = document.getElementById('contactPopup');
		const contactClose = document.getElementById('contactClose');

		contactButtons.forEach(button => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				if (contactPopup) {
					contactPopup.classList.add('active');
					document.body.style.overflow = 'hidden';
				}
			});
		});

		if (contactClose) {
			contactClose.addEventListener('click', () => {
				contactPopup.classList.remove('active');
				document.body.style.overflow = '';
			});
		}

		// Close popups when clicking on overlay
		const popupOverlays = document.querySelectorAll('.popup-overlay');
		popupOverlays.forEach(overlay => {
			overlay.addEventListener('click', (e) => {
				if (e.target === overlay) {
					overlay.classList.remove('active');
					document.body.style.overflow = '';
				}
			});
		});

		// Close popups with Escape key
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape') {
				popupOverlays.forEach(overlay => {
					if (overlay.classList.contains('active')) {
						overlay.classList.remove('active');
						document.body.style.overflow = '';
					}
				});
			}
		});
	};

	initPopupCards();

});
