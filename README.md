# Playful Sparkle - Google Sitemap for OpenCart 4

The **Playful Sparkle - Google Sitemap** extension for OpenCart 4.x+ offers a refined XML sitemap generation solution that aligns with search engine standards and SEO best practices. This extension supports detailed customization, allowing OpenCart merchants to tailor sitemaps to include specific product pages (with images), category pages (with images), manufacturer pages and information pages (and CMS topics, article pages), all tagged to support complete, accurate indexing by search engines.

A key feature of the extension is its use of PHP’s `XMLWriter` class to generate standards-compliant XML sitemaps per the [Sitemaps.org protocol](https://sitemaps.org/protocol.html). This ensures that search engines can efficiently crawl site content, aided by the `<lastmod>` attribute included on product and category pages. The `<lastmod>` attribute, which tracks the date of the most recent content update, allows search engines to prioritize updated pages, increasing the likelihood of timely indexing.

To streamline sitemap accessibility, the extension can automatically update the `.htaccess` file, enabling users and Google Search Console to access sitemaps using clean, SEO-friendly URLs like `/en-gb/sitemap.xml` instead of the default OpenCart URL format, such as `index.php?route=extension/feed/ps_google_sitemap&language=en-gb`. This improvement ensures compatibility with Google Search Console requirements and simplifies sitemap management.

Additionally, the extension validates the `robots.txt` file to verify whether the sitemap is allowed by Google. This proactive check notifies you early if adjustments to the file are needed, ensuring your sitemap is discoverable and functional for search engine indexing.

With multi-store and multi-language support, the extension allows merchants to create separate sitemaps for different store views and languages. This enables each store or language version of your site to have its own tailored sitemap, ensuring better indexing and visibility in search engines for audiences in different regions and languages.

---

## Features

- **Customizable Sitemap**: Customize the sitemap to include product pages (with images), category pages (with images), manufacturer pages and information pages (and CMS topis, article pages), ensuring thorough indexing.
- **Standards-Compliant XML Sitemap**: Generates a fully compliant XML sitemap using PHP’s XMLWriter class, following the [Sitemaps.org protocol](https://sitemaps.org/protocol.html).
- **Last Modified Tracking**: Includes the `<lastmod>` attribute for product and category pages, helping search engines track updates for timely indexing.
- **Compatible with Webmaster Tools**: Fully compatible with commonly used webmaster tools such as Google Search Console, Bing Webmaster Tools, and Yandex Webmaster Tools.
- **Compatibility**: Integrates smoothly with OpenCart 4.x+, supporting a range of online store setups.
- **Multi-store Support**: Supports multi-store setups by default, making it easy to integrate across multiple OpenCart stores.
- **Multilingual Support**: Ready for international use with languages including العربية (ar), فارسی (fa-ir), Български (bg), 中文(简体) (zh-cn), 中文(繁體) (zh-tw), Čeština (cs-cz), English (UK) (en-gb), English (US) (en-us), Français (fr-fr), Deutsch (de-de), Ελληνικά (el-gr), Magyar (hu-hu), Italiano (it-it), 日本語 (ja), 한국어 (ko-kr), Polski (pl-pl), Português (Brasil) (pt-br), Русский (ru-ru), Slovenčina (sk-sk), Español (es-es), and Türkçe (tr-tr).

---

## Installation Instructions

1. Download the latest version from this repository.
2. Log in to your OpenCart admin panel.
3. Navigate to `Extensions > Installer`.
4. Click the `Upload` button and upload the `ps_google_sitemap.ocmod.zip` file.
5. Locate the extension in the `Installed Extensions` list and click the `Install` button.
6. Navigate to `Extensions > Extensions` and select `Feeds` from the `Choose the extension type` dropdown list.
7. Locate the extension in the `Feeds` list and click the `Install` button.
8. Click the `Edit` button, configure the extension parameters, and click the `Save` button to save your settings.

---

## Support & Inquiries

For assistance or inquiries related to this extension, please open an issue on this repository, visit our [url=https://support.playfulsparkle.com]support website[/url], or contact us via email at [support@playfulsparkle.com](mailto:support@playfulsparkle.com).

---

## License

This project is distributed under the GPL-3.0 license. Please refer to the [LICENSE](./LICENSE) file for further details.

---

## Contributing

We encourage contributions from the community. To contribute, please fork the repository and submit a pull request with your proposed changes.
