<?php
// api/routes.php
// Maps URI paths to controller methods.

declare(strict_types=1);

function resolveRoute(string $method, array $segments): void {
    $ctrl    = $segments[0] ?? '';    // e.g. 'auth', 'departments', 'payments', 'users'
    $sub     = $segments[1] ?? '';    // e.g. 'login', 'logout', 'me', 'csrf'
    $idParam = isset($segments[2]) ? (int) $segments[2] : (
                    isset($segments[1]) && is_numeric($segments[1]) ? (int) $segments[1] : null
               );

    // ── DATABASE MIGRATION ROUTE ──────────────────────────────────────────
    if ($ctrl === 'run_migration') {
        require_once __DIR__ . '/run_migration.php';
        return;
    }


    // ── AUTH ──────────────────────────────────────────────────────────────
    if ($ctrl === 'auth') {
        $auth = new AuthController();
        match (true) {
            $method === 'POST' && $sub === 'register' => $auth->register(),
            $method === 'POST' && $sub === 'login'    => $auth->login(),
            $method === 'POST' && $sub === 'logout'   => $auth->logout(),
            $method === 'GET'  && $sub === 'me'       => $auth->me(),
            $method === 'GET'  && $sub === 'csrf'     => $auth->csrf(),
            default => jsonError('Auth route not found.', 404),
        };
        return;
    }

    // ── PAYMENTS ──────────────────────────────────────────────────────────
    if ($ctrl === 'payments') {
        $pay = new PaymentController();
        match (true) {
            $method === 'POST' && $sub === 'initiate'  => $pay->initiate(),
            $method === 'POST' && $sub === 'callback'  => $pay->callback(),
            $method === 'GET'  && $sub === ''          => $pay->list(),
            default => jsonError('Payment route not found.', 404),
        };
        return;
    }

    // ── MPESA (Safaricom async callback) ──────────────────────────────────
    // Safaricom POSTs the async STK result here; no session/auth needed.
    if ($ctrl === 'mpesa') {
        $pay = new PaymentController();
        match (true) {
            $method === 'POST' && $sub === 'callback' => $pay->callback(),
            default => jsonError('Mpesa route not found.', 404),
        };
        return;
    }


    // ── USERS (admin member management) ───────────────────────────────────
    if ($ctrl === 'users') {
        $actor = requireAdmin();
        $user  = new UserController();
        match (true) {
            $method === 'GET'    && $idParam === null => $user->list(),
            $method === 'GET'    && $idParam !== null => $user->get($idParam),
            $method === 'PUT'    && $idParam !== null => $user->update($idParam, $actor),
            $method === 'DELETE' && $idParam !== null && (isset($_GET['permanent']) || isset($_GET['hard'])) => $user->delete($idParam, $actor),
            $method === 'DELETE' && $idParam !== null => $user->deactivate($idParam, $actor),
            default => jsonError('User route not found.', 404),
        };
        return;
    }

    // ── UPLOAD ─────────────────────────────────────────────────────────────
    if ($ctrl === 'upload') {
        requireCsrf();
        $actor = requireAdmin();
        if ($method !== 'POST') {
            jsonError('Method not allowed.', 405);
        }
        if (!isset($_FILES['file'])) {
            jsonError('No file uploaded.', 400);
        }
        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            jsonError('File upload error: ' . $file['error'], 400);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $allowedTypes, true)) {
            jsonError('Invalid file type. Only JPG, PNG, WEBP, and GIF are allowed.', 400);
        }
        
        $uploadDir = __DIR__ . '/../assets/img/uploads';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        if (empty($ext)) {
            $ext = match ($mime) {
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif',
                default      => 'jpg',
            };
        }
        $filename = uniqid('img_', true) . '.' . $ext;
        $dest = $uploadDir . '/' . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            jsonResponse([
                'url' => 'assets/img/uploads/' . $filename
            ]);
        } else {
            jsonError('Failed to save uploaded file.', 500);
        }
        return;
    }

    // ── CONTENT (departments, ministries, leadership, sermons, events,
    //             weekly_meetings, resources, missions) ────────────────────
    $contentTables = [
        'departments', 'ministries', 'leadership',
        'sermons', 'events', 'weekly_meetings', 'resources', 'missions',
        'announcements', 'word_of_the_day',
    ];

    if (in_array($ctrl, $contentTables, true)) {
        $content = new ContentController();
        $actor   = null;
        match (true) {
            $method === 'GET'    && $idParam === null  => $content->list($ctrl),
            $method === 'GET'    && $idParam !== null  => $content->get($ctrl, $idParam),
            $method === 'POST'   && $idParam === null  => $content->create($ctrl, requireAdmin()),
            $method === 'PUT'    && $idParam !== null  => $content->update($ctrl, $idParam, requireAdmin()),
            $method === 'DELETE' && $idParam !== null  => $content->delete($ctrl, $idParam, requireAdmin()),
            default => jsonError("$ctrl route not found.", 404),
        };
        return;
    }

    jsonError('Endpoint not found.', 404);
}
