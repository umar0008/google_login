<?php
session_start();

require_once "library/vendor/autoload.php";

$google_client = new Google_Client();
$google_client->setHttpClient(new GuzzleHttp\Client(['verify' => 'C:/path/to/cacert.pem']));

// Set the OAuth 2.0 Client ID
$google_client->setClientId('544363044108-33dn5eh05btjtjlg82lkcd6c6dvrjf79.apps.googleusercontent.com');

// Set the OAuth 2.0 Client Secret key
$google_client->setClientSecret('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');

// Set the OAuth 2.0 Redirect URI
$google_client->setRedirectUri('http://localhost/google_login');

// Add scopes for email and profile
$google_client->addScope('email');
$google_client->addScope('profile');

if (isset($_GET['code'])) {
    try {
        // Attempt to exchange the authorization code for an access token
        $token = $google_client->fetchAccessTokenWithAuthCode($_GET['code']);

        if (!isset($token['error'])) {
            // Set the access token for the Google Client
            $google_client->setAccessToken($token['access_token']);

            // Store the access token in the session
            $_SESSION['access_token'] = $token['access_token'];

            // Create a service instance for Google OAuth2
            $google_service = new Google_Service_Oauth2($google_client);

            // Get user info
            $data = $google_service->userinfo->get();

            // Store user data in the session
            $_SESSION['first_name'] = $data['given_name'];
            $_SESSION['last_name'] = $data['family_name'];
            $_SESSION['email_address'] = $data['email'];
            $_SESSION['profile_picture'] = $data['picture'];
            $_SESSION['gender'] = isset($data['gender']) ? $data['gender'] : 'Not provided';
            $_SESSION['locale'] = isset($data['locale']) ? $data['locale'] : 'Not provided';
            $_SESSION['link'] = isset($data['link']) ? $data['link'] : 'Not provided';
            $_SESSION['hd'] = isset($data['hd']) ? $data['hd'] : 'Not provided';
        } else {
            throw new Exception("Error during token fetch: " . $token['error']);
        }
    } catch (Exception $e) {
        // Handle errors during token fetch
        echo 'An error occurred: ' . $e->getMessage();
    }
}

// Initialize the login button variable
$login_button = '';

// If the user is not logged in, create a login URL
if (!isset($_SESSION['access_token'])) {
    $login_button = '<a href="' . $google_client->createAuthUrl() . '"><img src="asset/sign-in-with-google.png" alt="Sign in with Google" /></a>';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login with Google in PHP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
    body {
        background-color: #f7f7f7;
    }

    .container {
        margin-top: 50px;
    }

    .panel {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }

    .panel-heading {
        background-color: #007bff;
        color: white;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 15px;
        font-size: 1.25rem;
        font-weight: bold;
    }

    .panel-body {
        padding: 25px;
    }

    .form-check-label {
        padding-left: 5px;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="text-center mb-4">Login using Google Account with PHP</h2>
        <div class="panel">
            <?php
            if (isset($_SESSION['access_token'])) {
                echo '<div class="panel-heading">Welcome User</div><div class="panel-body text-center">';
                echo '<img src="' . $_SESSION['profile_picture'] . '" class="img-responsive rounded-circle img-thumbnail mb-3" style="width: 150px; height: 150px;" />';
                echo '<h4><b>Name:</b> ' . $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] . '</h4>';
                echo '<h4><b>Email:</b> ' . $_SESSION['email_address'] . '</h4>';
                echo '<h4><b>Gender:</b> ' . $_SESSION['gender'] . '</h4>';
                echo '<h4><b>Locale:</b> ' . $_SESSION['locale'] . '</h4>';
                echo '<h4><b>Link:</b> ' . $_SESSION['link'] . '</h4>';
                echo '<h4><b>HD:</b> ' . $_SESSION['hd'] . '</h4>';
                echo '<a href="logout.php" class="btn btn-primary mt-3">Logout</a></div>';
            } else {
                echo '<div class="panel-body text-center">' . $login_button . '</div>';
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>