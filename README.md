# REDCap's LabArchives External Module

With collaboration from LabArchives, and to facilitate best practices for data storage, the MGB REDCap team envisioned and developed the LabArchives External Module (EM) as an interface into a user's LabArchives Notebooks. The EM meets essential LabArchives integration requirements, suppports customization at the insitution level (by a REDCap Administrator), and it can successfully upload REDCap reports into the user's LabArchives notebooks. 

## Expected Use-Case

The LabArchives EM enables a REDCap user to upload a REDCap report into their LabArchives notebook without leaving REDCap. Once a REDCap user establishes a connection between REDCap and LabArchives, the user can upload REDCap reports from any of their REDCap projects, into any LabArchives notebook they own.

## Workflow Overview

A user establishes a connection between their REDCap and LabArchives accounts. Once the connection is established, the user creates and views a report in REDCap, chooses the LabArchives notebook to which they want to send the report, then clicks a button to upload the report to LabArchives. If there isn't already a REDCap folder in the chosen notebook, the module will automatically create a REDCap folder in the LabArchives Notebook. If the REDCap folder already exists, the module will use that folder. Each time a user uploads to a notebook, a new page is created in the notebook's REDCap folder. The report is attached to the page as a CSV file. Once a report is uploaded, users are free to move, edit, or share it like any other page in LabArchives.

## Connect REDCap to Your LabArchives account

LabArchives provides a secure integration approach for its users to connect into their LabArchives account, using a unique, self-expiring token called an _App Authentication Token_. Upon successful connection, LabArchives returns a unique user ID that is used for transactions related to that user. REDCap's LabArchives module encrypts and stores this unique user ID for added security. The following are the steps required for successfully obtaining the required credentials for connecting a REDCap user to their LabArchives account:

1. **Log into REDCap**
2. **Access** one of your projects
3. **Go to** _Data Exports, Reports, and Stats_
4. **Select a report** 
5. **Click** **View Report** 
<br/>This opens the View Report tab, in the section labeled "Additional functionalities" 
6. **Click** _Upload to LabArchives_

If your LabArchives account has not yet been connected to REDCap, or the connection needs to be reset for some reason, a pop-up opens, with a set of steps for setting up the connection to LabArchives. The steps are:

+ **Click the link** to sign into your _LabArchives Account_, and sign into LabArchives,
+ In LabArchives, **navigate** to Profile >> External App Authentication
+ **Copy and paste** the _Email Address_ and _Password Token_ values from there into the _Email_ and _App Token_ fields in the REDCap popup.
+ **Select a Region**, if neecessary. The default is North America. Regions are for the LabArchives server where your account is located, not where you are at the moment. So if you work for an Australian company, but you're visiting France when setting up this connection, choose the Australian region.
+ **Click Connect**.

If the connection was unsuccessul, the current pop-up window will refresh, so the user can try re-entering their credentials from LabArchives.

If the connection was successful, a pop-up opens from which the user may choose a notebook, select their region (if needed), and initiate an upload. 

## Upload a REDCap Report to LabArchives

Once the user has established a connection to LabArchives, the pop-up will automatically open stating the credentials are ready to be used and the current report can be uploaded into LabArchives. The same occurs if the user is returning to their REDCap project at a later time. In short, the following steps outline how to upload a user's REDCap report to LabArchives:

1. **Log into REDCap**
2. **Access** one of your projects
3. **Go to** _Data Exports, Reports, and Stats_
4. **Select a report** 
5. **Click** _View Report_
+ **Make sure the data is de-identified**, if necessary, according to your institutional or departmental policies, grant requirements, or national privacy requirements

6. **Click** _Upload to LabArchives_
+ If the accounts are connected and notebooks are found, a pop-up opens from which you may choose a notebook, select your region (if needed), and initiate an upload. If there is an issue with the connection, or your email address was changed in LabArchives, the pop-up to connect your accounts opens. If that happens, click the button to disconnect the accounts, then repeat the process to connect accounts.
7. **Review** the report name and report id
+ This is the report that will be transferred
+ **Make sure it's the report you intend to send. You cannot permanently delete files from LabArchives.**
+ ** Must have a notebook prior uploading report.** 
8. Select the LabArchives Notebook to which you want to upload from the dropdown.
9. Once you are **certain** this is the right file and you've selected the right notebook, **click** _Upload_
<br/>The REDCap window reloads and displays a success message when the file is sent to the specified LabArchives notebook.

In the REDCap folder in your LabArchives Notebook, there will be a new page whose name includes a date-timestamp from the minute the file was uploaded. The uploaded REDCap report is attached to that page as a CSV file.

## Removing LabArchives Connection Credentials from REDCap

If you are using a shared computer, or you've changed your email address in LabArchives, or are having issues with uploading, you may need to disconnect and reconnect the two applications.

### To Disconnect your REDCap and LabArchives Accounts:

1. **Log into REDCap**
2. **Access** one of your projects
3. **Go to** _Data Exports, Reports, and Stats_
4. **Select a report** 
5. **Click** _View Report_
6. **Click** _Upload to LabArchives_
5. In the _Upload to LabArchives_ popup, **click** _Disconnect_ then **click** _Confirm_ to disconnect your REDCap account from your LabArchives account.

The next time you want to upload a file from REDCap to LabArchives, follow the instructions in "Connecting REDCap to Your LabArchives Account," above.


## Important Notes

### Must Own the Notebook 
Users can only upload to notebooks they **own**. Only notebooks you own will appear in the notebooks dropdown. A planned future enhancement will allow users to upload to notebooks they don't own, in which they have write access.

### Changing Your Email Address in LabArchives
In the current version of the EM, if you change your email address in LabArchives, REDCap will be unable to display the popup to select a notebook. If this happens, click the Disconnect button in the popup and re-connect the two apps with the new email address and password token from LabArchives. A fix for this is planned for a future version of the EM.

### Upload Button Doesn't Deactivate On-click
The upload button remains blue after you click it. If the upload is slow, it may look like nothing happened. When the upload is complete, REDCap will reload. A fix for this is planned for a future version of the EM.

### REDCap Uploads to a Folder Named REDCap in LabArchives
In LabArchives, if you rename the REDCap folder in a notebook, then the next time you upload to that notebook, REDCap will generate a new REDCap folder. Specifying a different folder for uploads is planned for a future enhancement.

### LabArchives Changes are Only Reflected After Reload in REDCap 
Your notebook list is loaded at the time you load the report page. If you rename a notebook in LabArchives, or add a notebook to LabArchives while you already have a REDCap report page open, **close** the _Upload to LabArchives_ popup and **refresh** the report page in REDCap, to get it to refresh the notebook list. Then **click** the _Upload to Labarchives_ button again.

### Live Filters not Included in Upload 
Live Filters in REDCap are a display feature of REDCap, and do not affect any exported reports. This means when you uploaded a report to LabArchives, it will contain all the report's data, regardless of whether or not you have used a life filter in REDCap. If you need a report to contain a limited data set, create a new report containing only the data you need.

### Repeated Message if You Refresh After Upload
If you refresh your REDCap window after a successful upload, the success message will re-appear and fade out again, but the file is not double-uploaded.

## FAQ
+ Can I use my LabArchives credentials on this EM?
+ No, your LabArchives password is not the same as the Authentication Token. A future upgrade may include a direct login option for SSO users.

# Information for REDCap Administrators

## Setting-up an Institution's LabArchives Credentials in REDCap

Before your users can upload from REDCap to LabArchives, your institution needs a LabArchives Enterprise license and credentials provided by LabArchives. Contact LabArchives to get credentials for your institution. Once you have them, follow the steps below:

1. On REDCap's Control Center, **navigate** to _External Modules_, and click **Configure** for the _LabArchives_ module.
+ For multi-national organizations, the module enables you to manage the credentials and region information for more than one region. Most likely, you have only one set of credentials.

_**LabArchives Current List of Region display names and their api urls are:**_
```
  Australia / New Zealand: https://auapi.labarchives.com
  Europe outside of UK:    https://euapi.labarchives.com
  United Kingdom:          https://ukapi.labarchives.com
  U.S. and rest of world:  https://api.labarchives.com
```
A future update will use an API call to automatically retrieve the full list from LabArchives.

2. **Specify** _Region's Display name_
3. **Specify** _Region's API URL_
4. **Specify** your institution's _AKID_ (provided by LabArchives)
5. **Specify** your institution's  _Password_ (provided by LabArchives)
6. **Specify** your institution's _SSO Entity ID_ (provided by LabArchives, if your organization uses SSO for logins)

If your organization has more than one set of credentials, add a new instance of the field named _Specify the available API regions:_ and repeat

Please consult your LabArchives representative if you have any questions about these items for your institution.

## REDCap Admin Tool for Disconnecting a User's REDCap Account from LabArchives

If you are a REDCap Administrator, you can disconnect a user's accounts via the EM link in the Control Center (named _LabArchives - Admin Control_). Enter the user's REDCap username, then click "Remove" to remove the user's LabArchives credentials.
