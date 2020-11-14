# Upgrade guide

- [Upgrading to 1.1 from 1.0](#upgrade-1.1)

<a name="upgrade-1.1"></a>
## Upgrading To 1.1

The settings and instructions for authorizing Google have been drastically simplified. For existing accounts, you will need to generate a new key file using these steps:

1. Log in to the [Google Developers Console](https://console.developers.google.com/home/dashboard) and do a search for `Service accounts`.
2. If you need to create a new `Service Account`, click on the `Create Service Account` at the top and then add a `name` and clicking on `Create`, you can skip setting up the `description` and `roles`.
3. You should see an account in the service accounts list and under the `Action` column is a hamburger menu. Open this menu and select `Create key`
4. Choose the `Key type` of `JSON`.
5. Download the file to your computer and upload it to the October back-end settings form.

The Profile ID number has also changed, follow these settings to find the new one:

1. In a new tab, navigate to the main [Google Analytics site](https://www.google.com/analytics/web/) and select the property you want to track.
2. Click the **Admin** main menu tab again and select **View > View Settings** from the menu. *Copy to your clipboard* the Profile ID (should be a number).
3. Paste this number in the **Analytics View/Profile ID number** field in the October back-end settings form.
