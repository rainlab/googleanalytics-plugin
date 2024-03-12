# Google Analytics 4 Integration Plugin

This plugin adds Google Analytics 4 tracking and reporting features to [October CMS](https://octobercms.com). This plugin requires October CMS 3.3 or above.

> **Note**: This plugin does not support Universal Analytics.

> **Important**: this plugin requires the [`bcmath` PHP extension](https://www.php.net/manual/en/book.bc.php). It is required by the Google PHP API.

If you are using October CMS v1 or v2, install v1.3 of the plugin with the following command:

```bash
composer require rainlab/googleanalytics-plugin "^1.3"
```

## Configuring the Google Analytics and Google Cloud accounts

To use the plugin, you will need to have a Google Cloud project with a service account, as well as the Google Analytics Data API enabled for that project. To begin, sign into the [Google Cloud Console](https://console.cloud.google.com/) and create or select your project.

To create a service account in a Google Cloud project:

* Open the [Credentials page](https://console.developers.google.com/project/_/apiui/credential) and select your project.
* Click the **Create credentials** button and select **Service account**.
* Enter a name for the service account, e.g. "October CMS GA". Enter an ID for the service account, e.g. "october-cms-ga".
* Click **Done**. This will create the service account and redirect back to the account list.

To generate and download a private key file for the service account:

* Click the account you just created in the Service Accounts list.
* Click **Keys** in the top menu.
* Click **Add Key** / **Create new key** and select **JSON**. This will download the JSON file. You will need this file later to configure the plugin.

The plugin requires Google Analytics Data API to be enabled for the Google Cloud project. This is needed to fetch the analytics data and display it on the October CMS dashboard. To enable the API:

* Return to the project dashboard page in the Google Cloud Console.
* Click **Enabled APIs & services** in the sidebar.
* Click **Enable APIs and services** in the top menu.
* Search for "Google Analytics Data API".
* Click the found API and enable it.

You can alternatively use [this link](https://console.cloud.google.com/apis/enableflow?apiid=analyticsdata.googleapis.com&credential=client_key), but make sure you're enabling the API for the correct project.

Next, you need to give the service account access to your Google Analytics account:

* In the [Google Cloud dashboard](https://console.cloud.google.com/home/dashboard) select your project, click **APIs & Services** and then **Credentials** in the sidebar.
* In the Service Accounts list, copy email of the service account you created.
* Go to [Google Analytics](https://analytics.google.com/) and select a GA4 property you want to work with.
* In the left-hand menu, click **Admin**.
* In the **Property** column, click on **Property Access Management**.
* Click the **Add +** button and enter the email address of the service account.
* Select the **Viewer** permission for the service account.
* Click the **Add** button to save the changes.

## Configuring the plugin

* Open your October CMS installation Administration Area and go to **Settings** / **Google Analytics**.
* Upload the JSON file you downloaded before to the **Private key** field.
* To find the **Analytics Property ID** - go to your [Google Analytics](https://analytics.google.com/) property's **Admin** page and click **Property Settings**. Copy the **Property ID** value from the page and paste it into the corresponding field on the plugin settings page.
* To find the **Measurement ID** value you first need to create a **Data Stream** on the Admin page of your Google Analytics 4 property. After creating a stream, click it in the stream list and copy the **Measurement ID** value. Paste it into the corresponding field on the plugin settings page.
* Save the settings.

## Installing the Google Analytics tracking code

You can use Google Tag Manager to install the tracker code. Below is an explanation how to install the tracker code manually.

* Drop the Google Analytics Tracker component to your CMS layout.
* Add the following code to the layout immediately after the <head> element:

```yaml
{% component 'googleTracker' %}
```

After installing the tracker, you can add Google Analytics dashboard widgets and preview the traffic statistics without leaving October CMS.

## Compatibility with Multisite

If every site should have its own analytics settings and widgets, open the **config/multisite.php** file and enable the setting below. Change the value to `true` to enable unique configuration for each site definition.

```php
'features' => [
    // ...
    'rainlab_googleanalytics_setting' => false,
]
```

## Troubleshooting

### Fix for Windows / XAMPP

**cURL error 60: SSL certificate problem: unable to get local issuer certificate**

1. Follow this link: http://curl.haxx.se/ca/cacert.pem and save it in a file called `cacert.pem`.

1. Open your `php.ini` file insert or edit the following line:
    ```
    curl.cainfo = "[pathtothisfile]\cacert.pem"
    ```

1. Restart Apache

### Popup message simply saying "error" when trying to add the JSON key file.

Some ad-blockers, such as Chrome's uBlock Origin extension or Firefox's own built-in algorithms, may prevent the uploading of the JSON file key. Disabling these, or whitelisting the October CMS website, may resolve this issue for you.

### License

This plugin is an official extension of the October CMS platform and is free to use if you have a platform license. See [EULA license](https://octobercms.com/eula) for more details.
