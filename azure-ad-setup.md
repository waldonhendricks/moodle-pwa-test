# Azure AD (Microsoft Entra ID) Single Sign-On Setup

This document outlines the requirements and setup instructions for integrating Moodle with Azure Active Directory (now Microsoft Entra ID) to authenticate users and sync their South African ID Number.

## 1. Purpose
Unlike on-premise Active Directory (which uses LDAP service accounts), Azure AD uses modern authentication protocols (OpenID Connect / OAuth 2.0). 
Moodle requires an **App Registration** in the Azure Portal to:
- Authenticate users via Microsoft.
- Synchronize user profile fields.
- Map the user's South African ID Number from an Azure AD attribute (like `employeeId` or a custom extension attribute) to Moodle's `idnumber` field. This is required for the PWA backend to generate the single-use login URL.

---

## 2. Azure App Registration (For the Azure Administrator)

The Azure/IT Administrator must perform these steps in the Microsoft Entra ID (Azure AD) Portal.

### Step 2.1: Register the Application
1. Go to **Microsoft Entra ID** > **App registrations** > **New registration**.
2. **Name:** SmartStart Moodle LMS
3. **Supported account types:** Accounts in this organizational directory only (Single tenant).
4. **Redirect URI:** Select **Web** and enter your Moodle OIDC redirect URL:
   - Example: `https://learn.smartstart.org.za/auth/oidc/`
5. Click **Register**.

### Step 2.2: Gather Required Information for Moodle
After registration, copy the following values and provide them to the Moodle Administrator:
- **Application (client) ID**
- **Directory (tenant) ID**

### Step 2.3: Generate a Client Secret
1. Go to **Certificates & secrets** > **New client secret**.
2. **Description:** Moodle OIDC Secret
3. **Expires:** 24 months (or per organization policy).
4. **Important:** Copy the **Value** immediately. This is the **Client Secret**. It will be hidden once you leave the page. Provide this securely to the Moodle Administrator.

### Step 2.4: Grant API Permissions
Moodle needs permission to read user profiles to sync the ID number.
1. Go to **API permissions** > **Add a permission** > **Microsoft Graph**.
2. Select **Application permissions**.
3. Check **User.Read.All** (Allows Moodle to sync user attributes).
4. Check **Directory.Read.All** (Optional, but recommended for full sync capabilities).
5. Click **Add permissions**.
6. **CRITICAL:** Click **Grant admin consent for [Your Tenant Name]**. The status must show a green checkmark.

### Step 2.5: Configure Token Claims (Passing the SA ID Number)
Azure AD must send the user's SA ID Number to Moodle when they log in.
1. Go to **Token configuration** > **Add optional claim**.
2. Select **ID** token type.
3. Select the attribute that holds the SA ID Number (e.g., `employeeId` or an extension attribute).
4. Click **Add**.

---

## 3. Moodle Configuration (For the Moodle Administrator)

Moodle requires the **Microsoft 365 Integration** plugins, specifically the **OpenID Connect (auth_oidc)** plugin.

### Step 3.1: Configure the OIDC Plugin
1. Navigate to: `Site administration > Plugins > Authentication > OpenID Connect`.
2. **Client ID:** Enter the Application (client) ID from Azure.
3. **Client Secret:** Enter the Client Secret from Azure.
4. **Tenant:** Enter the Directory (tenant) ID from Azure.
5. **Authorization Endpoint / Token Endpoint:** Leave as default (Microsoft Graph).

### Step 3.2: Map the SA ID Number (Data Mapping)
You must map the Azure claim to Moodle's `idnumber` field so the PWA backend can query it.
1. In the OIDC plugin settings, scroll down to the **Data Mapping** section.
2. Find the **ID number** field (`idnumber`).
3. Set the **Mapping** to the exact claim name configured in Azure AD (Step 2.5). 
   - Example: `employeeId` or `extension_xxxxx_saIdNumber`.
4. Set **Update local** to `On every login` to ensure Moodle always has the latest ID number from Azure.

---

## 4. How this works with the PWA (`auth_userkey`)
1. The user's account is provisioned in Moodle via Azure AD, and their SA ID Number is synced into Moodle's `idnumber` field.
2. When the user opens the PWA, the PWA knows their SA ID Number.
3. The PWA backend calls the Moodle API (via the `auth_userkey_request_login_url` function) passing that exact SA ID Number.
4. Because the ID number matches what Azure AD synced into Moodle, Moodle successfully generates the single-use `loginurl` for the iframe.