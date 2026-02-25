# Active Directory Services (ADS) Service Account Setup

This document outlines the requirements and setup instructions for the Active Directory (ADS) Service Account needed for the Moodle / PWA integration.

## 1. Purpose
Moodle requires a dedicated service account (often called an LDAP Bind Account) to securely connect to Active Directory. This allows Moodle to:
- Authenticate users.
- Synchronize user profile fields.
- Crucially, map the user's South African ID Number from AD to Moodle's `idnumber` field, which the PWA backend requires to generate the single-use login URL.

## 2. Information Required from IT / Infrastructure
To configure Moodle to talk to ADS, the development team requires the following details from the IT department:

| Requirement | Description / Example |
| --- | --- |
| **Server URI** | The hostname or IP of the AD server. <br>*Preferably LDAPS: `ldaps://ad.smartstart.org.za:636`* |
| **Service Account Username** | The Distinguished Name (DN) or UPN of the service account. <br>*Example: `CN=svc_moodle_sync,OU=ServiceAccounts,DC=smartstart,DC=org,DC=za`* |
| **Service Account Password** | A strong, non-expiring password. |
| **Contexts (Base DN)** | The OUs where the actual user accounts reside. <br>*Example: `OU=Users,OU=SmartStart,DC=smartstart,DC=org,DC=za`* |
| **ID Number Attribute** | The specific Active Directory field where the SA ID Number is stored. <br>*Example: `employeeID`, `employeeNumber`, or `extensionAttribute1`* |

---

## 3. Creating the Service Account (For the AD Administrator)

When creating this account in Active Directory Users and Computers (ADUC), please adhere to the following strict security practices:

### Step 3.1: Account Creation
1. Create a new User object in a dedicated Service Accounts OU.
2. **First Name:** Moodle
3. **Last Name:** AD Sync
4. **User logon name:** `svc_moodle_sync`

### Step 3.2: Password & Expiration
1. Generate a complex password (at least 16+ characters).
2. **Check:** `User cannot change password`
3. **Check:** `Password never expires` (Critical: If this expires, the Moodle PWA integration will break).

### Step 3.3: Permissions (Principle of Least Privilege)
1. **DO NOT** add this account to Domain Admins, Account Operators, or any elevated groups.
2. By default, standard "Domain Users" have read-only access to directory attributes. This default read access is usually sufficient for Moodle to bind and read user fields.
3. If your Active Directory is locked down, manually delegate **Read All Properties** access for this service account to the specific OUs where the Moodle users reside.

### Step 3.4: Configure the SA ID Number Field
Ensure that whatever field is chosen to house the SA ID Number (e.g., `employeeNumber`) is populated for all users who need to access the PWA.

---

## 4. Moodle Configuration (For the Moodle Administrator)
Once the IT team provides the details from Section 2, configure Moodle:

1. Navigate to: `Site administration > Plugins > Authentication > LDAP server`
2. **Host URL:** Enter the Server URI (`ldaps://...`)
3. **Version:** `3`
4. **Distinguished name:** Enter the Service Account DN.
5. **Password:** Enter the Service Account Password.
6. **Contexts:** Enter the Base DNs provided by IT.
7. **Data Mapping:** Scroll down to the **Data Mapping** section. Find the `ID number` field (`idnumber`) and set its mapping to the AD attribute specified by IT (e.g., `employeeNumber`). Set update on login to `Yes`.