# WPML Link Manager
> Makes the plugin Link Manager compatible with WPML translating features

## Minimum requirements
* **Sitepress Multilingual CMS** installed and activated
* **WPML Translation management** installed and activated
* **WPML String translation** installed and activated
* **Link Manager plugin** installed and activated (available in the [WordPress official repository](https://wordpress.org/plugins/link-manager/))

## Installation
1. Download **WPML Link Manager** folder and paste it in `wp-content/plugins`
2. In **Plugins** admin page, activate **WPML Link Manager**

## How it works?
Basically, all link information such as link title, link description and category title are translated as strings.
So all the links (already existing or added) are considered in the default language of the site.
The link related strings are translatable in **WPML > String translation**

1. Create (or edit) a link in **Links** admin page.
2. Go to **WPML > String translation**
3. Select the domain related to this link (`link-manager-link-{link_id}`)
4. Translate the title and description in secondary languages

The same process applies for link categories:

1. Create (or edit) a link category in **Links** admin page.
2. Go to **WPML > String translation**
3. Select the domain related to this link category (`link-manager-category-{category_id}`)
4. Translate the title and description in secondary languages

