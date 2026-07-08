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

    // ── USERS (admin member management) ───────────────────────────────────
    if ($ctrl === 'users') {
        $actor = requireAdmin();
        $user  = new UserController();
        match (true) {
            $method === 'GET'    && $idParam === null => $user->list(),
            $method === 'GET'    && $idParam !== null => $user->get($idParam),
            $method === 'PUT'    && $idParam !== null => $user->update($idParam, $actor),
            $method === 'DELETE' && $idParam !== null => $user->deactivate($idParam, $actor),
            default => jsonError('User route not found.', 404),
        };
        return;
    }

    // ── CONTENT (departments, ministries, leadership, sermons, events,
    //             weekly_meetings, resources, missions) ────────────────────
    $contentTables = [
        'departments', 'ministries', 'leadership',
        'sermons', 'events', 'weekly_meetings', 'resources', 'missions',
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
