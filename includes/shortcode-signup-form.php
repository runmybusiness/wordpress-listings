<?php

add_shortcode('rmb-signup', 'rmb_signup_form_handler');

function rmb_signup_form_handler($attr = [])
{
    if ($_POST['rmb-signup-submit']) {
        if (rmb_signup_handle_post($attr)) {
            echo '<strong>Thanks for signing up! A confirmation email has been sent to your email address.</strong>';
        } else {
            echo '<strong>There was an error processing your request. Please try again...</strong><br><br>';
            return rmb_signup_show_form($attr);
        }
    } else {
        return rmb_signup_show_form($attr);
    }
}

function rmb_signup_handle_post($attr)
{
    if (isset($_POST['rmb-signup-submit'])) {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $organization = $_POST['organization'];
        $email = $_POST['email'];
        $groups = [$attr['group']];
        $dataToPass = [
            'email' => $email,
            'groups' => $groups,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'organization' => $organization,
            ];
        $dataToPass = http_build_query($dataToPass);

        $ch = curl_init();

        $runmybusiness_options = get_option('runmybusiness_options');
        $username = $runmybusiness_options['runmybusiness_username'];
        $password = $runmybusiness_options['runmybusiness_password'];

        curl_setopt($ch, CURLOPT_URL, $attr['signup_url']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataToPass);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //timeout after 30 seconds
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

        $output = curl_exec($ch);

        curl_close($ch);

        if (!empty($output)) {
            $result = json_decode($output, true);
            $success = $result['success'];

            return $success;
        }

        return false;
    }
}

function rmb_signup_show_form($attr = [])
{
    $form = <<<HTMLPLUGIN
<form method="post">
<p>
First Name
<br>
<input type="text" required="true" name="firstname">
</p>

<p>
Last Name
<br>
<input type="text" required="true" name="lastname">
</p>

<p>
Email
<br>
<input type="text" required="true" name="email">
</p>

<p>
Company / Organization
<br>
<input type="text" required="true" name="organization">
</p>
<p><button type="submit" name="rmb-signup-submit" value="submit">Add to Mailing List!</button>
</form>
HTMLPLUGIN;

    return $form;
}
