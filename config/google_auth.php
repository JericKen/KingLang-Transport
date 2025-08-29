<?php
/**
 * Google OAuth Configuration
 * 
 * This file contains the configuration settings for Google OAuth authentication.
 * You need to create a project in the Google Developer Console and enable the Google+ API.
 * Then, create OAuth 2.0 credentials and add the client ID here.
 * 
 * @link https://console.cloud.google.com/apis/credentials
 */

// Google OAuth Client ID (replace with your actual client ID from Google Developer Console)
define('GOOGLE_CLIENT_ID', '52731091194-88ihim5onhcu8ipd6bu41mfi8i3vfut7.apps.googleusercontent.com');

// Redirect URI after Google authentication
define('GOOGLE_REDIRECT_URI', 'http://localhost:9999/client/google-callback'); 