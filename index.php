<?php

$cookieName = "user";
$adminCookie = "YWRtaW4="; // Base64("admin")

// Login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($username === "admin" && $password === "admin") {
        setcookie(
            $cookieName,
            $adminCookie,
            [
                "expires" => time() + 3600,
                "path" => "/",
                "secure" => isset($_SERVER["HTTPS"]),
                "httponly" => true,
                "samesite" => "Lax"
            ]
        );

        header("Location: /");
        exit;
    }

    $error = "Invalid username or password";
}

// Logout
if (isset($_GET["logout"])) {
    setcookie(
        $cookieName,
        "",
        [
            "expires" => time() - 3600,
            "path" => "/"
        ]
    );

    header("Location: /");
    exit;
}

$loggedIn =
    isset($_COOKIE[$cookieName]) &&
    $_COOKIE[$cookieName] === $adminCookie;

// Trigger callback only for logged-in users
if (
    $loggedIn &&
    isset($_GET["kill"]) &&
    $_GET["kill"] === "poc"
) {
    $callbackUrl =
        "https://ytvj8eqyypgbqd6wxnkrs7pppgv7jx7m.oastify.com";

    $ch = curl_init($callbackUrl);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => "poc was killed",
        CURLOPT_HTTPHEADER => [
            "Content-Type: text/plain"
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5
    ]);

    curl_exec($ch);
    curl_close($ch);

    $callbackSent = true;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>101</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

<?php if (!$loggedIn): ?>

    <div class="card">

        <h1>Login</h1>

        <p class="subtitle">
            Login to continue
        </p>

        <?php if (isset($error)): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <input
                type="text"
                name="username"
                placeholder="Username"
                required
            >

            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >

            <button type="submit">
                Login
            </button>

        </form>

        <div class="hint">
            admin : admin
        </div>

    </div>

<?php else: ?>

    <div class="card">

        <h1>Welcome Admin</h1>

        <p class="success">
            You are logged in.
        </p>

        <?php if (isset($callbackSent)): ?>

            <div class="callback">
                PoC callback sent.
            </div>

        <?php endif; ?>

        <p>
            Try:
        </p>

        <code>
            /?kill=poc
        </code>

        <a
            class="button"
            href="/?kill=poc"
        >
            Trigger PoC
        </a>

        <a
            class="logout"
            href="/?logout=1"
        >
            Logout
        </a>

    </div>

<?php endif; ?>

</div>

</body>
</html>
