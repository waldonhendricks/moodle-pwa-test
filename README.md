# Moodle PWA Integration
This repository contains the backend and frontend code to securely integrate a PWA with Moodle using the auth_userkey plugin.

## Setup Instructions
Please refer to the detailed configuration steps outlined for setting up the `auth_userkey` plugin in Moodle, creating a service account, and generating the necessary tokens.

## Files Included
- `/backend/get-moodle-url.php`: The PHP backend proxy script.
- `/frontend/MoodleIframe.jsx`: The React component to fetch the single-use URL and embed Moodle.
