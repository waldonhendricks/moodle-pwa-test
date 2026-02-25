# Azure AD (Microsoft Entra ID) Single Sign-On Setup Instructions

**To:** Azure Administrator  
**From:** SmartStart Development Team  
**Subject:** Configuration required for Moodle LMS Single Sign-On (SSO) and PWA Integration

This document outlines the exact steps required in the Microsoft Entra ID (Azure AD) Portal to configure Single Sign-On for our Moodle Learning Management System. 

This configuration allows users to authenticate into Moodle seamlessly and ensures their South African ID Number is synced to Moodle, which is a hard requirement for the SmartStart PWA integration.

---

## Part 1: Create the App Registration

Moodle requires a dedicated App Registration to establish a trust relationship with Microsoft Entra ID.

1. Navigate to **Microsoft Entra ID** > **App registrations**.
2. Click **New registration**.
3. **Name:** Enter a descriptive name (e.g., `SmartStart Moodle LMS`).
4. **Supported account types:** Select **Accounts in this organizational directory only** (Single tenant).
5. **Redirect URI:**  
   * Platform: Select **Web**  
   * URI: Enter our Moodle OIDC callback URL: `https://learn.smartstart.org.za/auth/oidc/` *(Note: If we have a separate test/staging environment, please add that URI here as well).*  
6. Click **Register**.

**[Action Required]** Please copy the **Application (client) ID** and the **Directory (tenant) ID** from the Overview page. You will need to provide these to the Moodle Administrator.

---

## Part 2: Generate the Client Secret

Moodle needs a client secret to authenticate its API calls to Azure.

1. Within the new App Registration, go to **Certificates & secrets** in the left menu.
2. Click **New client secret**.
3. **Description:** Enter `Moodle OIDC Secret`.
4. **Expires:** Select an appropriate expiration per our organization's security policy (e.g., 24 months).
5. Click **Add**.

**[CRITICAL Action Required]** Copy the **Value** of the client secret immediately. It will be permanently hidden once you navigate away from this page. You must provide this securely to the Moodle Administrator.

---

## Part 3: Grant API Permissions

Moodle must be granted permission to read user profiles in order to sync the South African ID Number and other basic profile fields during login.

1. Go to **API permissions** in the left menu.
2. Click **Add a permission** > select **Microsoft Graph**.
3. Select **Application permissions**.
4. Search for and check the following permission:
   * `User.Read.All` (Required to sync user attributes)
5. Click **Add permissions**.

**[CRITICAL Action Required]** You must click the **Grant admin consent for [Tenant Name]** button. The status column must show a green checkmark indicating consent is granted.

---

## Part 4: Configure Token Claims (The SA ID Number)

This is the most critical step for the PWA integration. When a user logs in, Azure AD must bundle their South African ID Number into the authentication token sent to Moodle.

1. Identify which Active Directory attribute currently stores the users' SA ID Number (e.g., `employeeId`, `employeeNumber`, or a custom extension attribute like `extension_xxxxx_saIdNumber`).
2. Go to **Token configuration** in the left menu.
3. Click **Add optional claim**.
4. Token type: Select **ID**.
5. Select the specific attribute identified in step 1 from the list.
6. Click **Add**.

**[Action Required]** Please document the exact name of the claim you configured (e.g., "The SA ID Number is being sent in the `employeeId` claim"). The Moodle Administrator needs this exact string to map the data correctly.

---

## Summary of Handover Items

When the above steps are complete, please securely provide the following four (4) items to the Moodle Administrator:

1. **Application (client) ID:** `________________________________`
2. **Directory (tenant) ID:** `________________________________`
3. **Client Secret Value:** `________________________________`
4. **The Claim Name containing the SA ID:** `________________________________`

Thank you!