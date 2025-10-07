# Setting up Google Sign-In for Kinglang Booking System

This guide will help you set up Google Sign-In for the Kinglang Booking System.

## Prerequisites

1. A Google account with access to Google Cloud Console
2. The Kinglang Booking System installed and running

## Steps to Set Up Google Sign-In

### 1. Create a Google Cloud Project

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Click on "Select a project" at the top of the page
3. Click on "New Project"
4. Enter a name for your project (e.g., "Kinglang Booking")
5. Click "Create"

### 2. Configure OAuth Consent Screen

1. In your Google Cloud Project, go to "APIs & Services" > "OAuth consent screen"
2. Select "External" as the user type (unless you have a Google Workspace account)
3. Click "Create"
4. Fill in the required information:
   - App name: "Kinglang Booking"
   - User support email: Your email address
   - Developer contact information: Your email address
5. Click "Save and Continue"
6. On the "Scopes" page, click "Add or Remove Scopes"
7. Add the following scopes:
   - `https://www.googleapis.com/auth/userinfo.email`
   - `https://www.googleapis.com/auth/userinfo.profile`
8. Click "Save and Continue"
9. Add test users if needed, then click "Save and Continue"
10. Review your settings and click "Back to Dashboard"

### 3. Create OAuth 2.0 Client ID

1. In your Google Cloud Project, go to "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth client ID"
3. Select "Web application" as the application type
4. Enter a name for the client (e.g., "Kinglang Booking Web Client")
5. Under "Authorized JavaScript origins", add your website's domain (e.g., `http://localhost:9999`)
6. Under "Authorized redirect URIs", add your redirect URI (e.g., `http://localhost:9999/client/google-callback`)
7. Click "Create"
8. Note the "Client ID" - you will need this for the next step

### 4. Configure the Application

1. Open the `config/google_auth.php` file in the Kinglang Booking System
2. Replace `YOUR_GOOGLE_CLIENT_ID` with the Client ID you obtained in the previous step
3. Save the file

### 5. Apply Database Migration

Run the SQL migration to add the required fields to the users table:

```sql
-- Add Google authentication fields to users table
ALTER TABLE `users` 
ADD COLUMN `google_id` VARCHAR(100) NULL,
ADD COLUMN `profile_picture` VARCHAR(255) NULL;

-- Add index for faster Google ID lookups
ALTER TABLE `users` ADD INDEX `google_id_index` (`google_id`);
```

### 6. Test the Integration

1. Go to your login page
2. Click on the "Sign in with Google" button
3. Follow the Google authentication flow
4. You should be redirected back to your application and logged in

## Troubleshooting

- If you see "Invalid Client ID" errors, make sure you've correctly copied your Client ID to the `config/google_auth.php` file
- If you see "redirect_uri_mismatch" errors, make sure your authorized redirect URI in the Google Cloud Console matches the one in your application
- Check the browser console for any JavaScript errors
- Check your server logs for PHP errors

## Additional Resources

- [Google Sign-In for Websites documentation](https://developers.google.com/identity/gsi/web)
- [Google OAuth 2.0 for Web Server Applications](https://developers.google.com/identity/protocols/oauth2/web-server) 