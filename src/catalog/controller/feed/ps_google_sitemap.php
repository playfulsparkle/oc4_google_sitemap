<?php
namespace Opencart\Catalog\Controller\Extension\PSGoogleSitemap\Feed;
/**
 * Class PSGoogleSitemap
 *
 *
 * This class generates an XML sitemap for Google, including products, categories,
 * manufacturers, and information pages. The sitemap is created based on the configuration
 * settings and the active languages in the OpenCart store.
 *
 * @package Opencart\Catalog\Controller\Extension\PSGoogleSitemap\Feed
 */
class PSGoogleSitemap extends \Opencart\System\Engine\Controller
{
    /**
     * Generates and outputs the Google Sitemap XML.
     *
     * This method checks if the sitemap feature is enabled in the configuration.
     * If it is, it initializes the XMLWriter, sets the XML header, and populates
     * the sitemap with URLs for products, categories, manufacturers, and
     * information pages based on the active languages.
     *
     * @return void
     */
    public function index(): void
    {
        if (!$this->config->get('feed_ps_google_sitemap_status')) {
            return;
        }

        $this->load->model('tool/image');
        $this->load->model('localisation/language');
        $this->load->model('extension/ps_google_sitemap/feed/ps_google_sitemap');

        $languages = $this->model_localisation_language->getLanguages();

        if (isset($this->request->get['language']) && isset($languages[$this->request->get['language']]['code'])) {
            $language = $languages[$this->request->get['language']]['code'];
        } else {
            $language = $this->config->get('config_language');
        }


        $this->load->model('setting/setting');

        $config = $this->model_setting_setting->getSetting('feed_ps_google_sitemap', $this->config->get('config_store_id'));

        $sitemap_product = isset($config['feed_ps_google_sitemap_product']) ? (bool) $config['feed_ps_google_sitemap_product'] : false;
        $sitemap_product_images = isset($config['feed_ps_google_sitemap_product_images']) ? (bool) $config['feed_ps_google_sitemap_product_images'] : false;
        $sitemap_max_product_images = isset($config['feed_ps_google_sitemap_max_product_images']) ? (int) $config['feed_ps_google_sitemap_max_product_images'] : 1;
        $sitemap_category = isset($config['feed_ps_google_sitemap_category']) ? (bool) $config['feed_ps_google_sitemap_category'] : false;
        $sitemap_category_images = isset($config['feed_ps_google_sitemap_category_images']) ? (bool) $config['feed_ps_google_sitemap_category_images'] : false;
        $sitemap_manufacturer = isset($config['feed_ps_google_sitemap_manufacturer']) ? (bool) $config['feed_ps_google_sitemap_manufacturer'] : false;
        $sitemap_manufacturer_images = isset($config['feed_ps_google_sitemap_manufacturer_images']) ? (bool) $config['feed_ps_google_sitemap_manufacturer_images'] : false;
        $sitemap_information = isset($config['feed_ps_google_sitemap_information']) ? (bool) $config['feed_ps_google_sitemap_information'] : false;
        $sitemap_topic = isset($config['feed_ps_google_sitemap_topic']) ? (bool) $config['feed_ps_google_sitemap_topic'] : false;
        $sitemap_article = isset($config['feed_ps_google_sitemap_article']) ? (bool) $config['feed_ps_google_sitemap_article'] : false;

        $is_oc_4_1 = version_compare(VERSION, '4.1.0.0', '>=');

        $separator = version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';

        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->setIndentString("\t");
        $xml->startDocument('1.0', 'UTF-8', 'yes');

        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $xml->writeAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
        $xml->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');

        #region Product
        if ($sitemap_product) {
            // Fetch products in chunks to handle large datasets
            $products = $this->model_extension_ps_google_sitemap_feed_ps_google_sitemap->getProducts();

            if ($sitemap_product_images && $sitemap_max_product_images > 1) {
                $product_images = $this->model_extension_ps_google_sitemap_feed_ps_google_sitemap->getProductImages(
                    array_column($products, 'product_id'),
                    $sitemap_max_product_images
                );
            } else {
                $product_images = [];
            }

            foreach ($products as $product) {
                $xml->startElement('url');
                $product_url = $this->url->link('product/product', 'language=' . $language . '&product_id=' . $product['product_id']);
                $xml->writeElement('loc', str_replace('&amp;', '&', $product_url));
                $xml->writeElement('lastmod', date('Y-m-d\TH:i:sP', strtotime($product['date_modified'])));

                if ($sitemap_product_images && $sitemap_max_product_images > 0) {
                    $resized_image = !empty($product['image']) ? $this->model_tool_image->resize($product['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) : null;

                    if ($resized_image) {
                        $xml->startElement('image:image');
                        $xml->writeElement('image:loc', $resized_image);
                        $xml->endElement();
                    }

                    if (isset($product_images[(int) $product['product_id']])) {
                        foreach ($product_images[(int) $product['product_id']] as $product_image) {
                            $resized_image = !empty($product_image['image']) ? $this->model_tool_image->resize($product_image['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) : null;

                            if ($resized_image) {
                                $xml->startElement('image:image');
                                $xml->writeElement('image:loc', $resized_image);
                                $xml->endElement();
                            }
                        }
                    }
                }

                $xml->endElement();
            }
        }
        #endregion


        #region Category
        if ($sitemap_category) {
            $this->getCategories($xml, $sitemap_category_images, $language, 0);
        }
        #endregion

        #region Manufacturer
        if ($sitemap_manufacturer) {
            $manufacturers = $this->model_extension_ps_google_sitemap_feed_ps_google_sitemap->getManufacturers();

            foreach ($manufacturers as $manufacturer) {
                $xml->startElement('url');
                $manufacturer_url = $this->url->link('product/manufacturer' . $separator . 'info', 'language=' . $language . '&manufacturer_id=' . $manufacturer['manufacturer_id']);
                $xml->writeElement('loc', str_replace('&amp;', '&', $manufacturer_url));

                if ($sitemap_manufacturer_images) {
                    $resized_image = !empty($manufacturer['image']) ? $this->model_tool_image->resize($manufacturer['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) : null;

                    if ($resized_image) {
                        $xml->startElement('image:image');
                        $xml->writeElement('image:loc', $resized_image);
                        $xml->endElement();
                    }
                }

                $xml->endElement();
            }
        }
        #endregion

        #region Information
        if ($sitemap_information) {
            $informations = $this->model_extension_ps_google_sitemap_feed_ps_google_sitemap->getInformations();

            foreach ($informations as $information) {
                $xml->startElement('url');
                $information_url = $this->url->link('information/information', 'language=' . $language . '&information_id=' . $information['information_id']);
                $xml->writeElement('loc', str_replace('&amp;', '&', $information_url));
                $xml->endElement();
            }
        }
        #endregion

        if ($is_oc_4_1) {
            #region Topic
            if ($sitemap_topic) {
                $topics = $this->model_extension_ps_google_sitemap_feed_ps_google_sitemap->getTopics();

                foreach ($topics as $topic) {
                    $xml->startElement('url');
                    $topic_url = $this->url->link('cms/blog', 'language=' . $language . '&topic_id=' . $topic['topic_id']);
                    $xml->writeElement('loc', str_replace('&amp;', '&', $topic_url));
                    $xml->endElement();
                }
            }
            #endregion

            #region Articla
            if ($sitemap_article) {
                $articles = $this->model_extension_ps_google_sitemap_feed_ps_google_sitemap->getArticles();

                foreach ($articles as $article) {
                    $xml->startElement('url');
                    $article_url = $this->url->link('cms/blog.info', 'language=' . $language . '&topic_id=' . $article['topic_id'] . '&article_id=' . $article['article_id']);
                    $xml->writeElement('loc', str_replace('&amp;', '&', $article_url));
                    $xml->endElement();
                }
            }
            #endregion
        }

        $xml->endElement();
        $xml->endDocument();

        $this->response->addHeader('Content-Type: application/xml; charset=utf-8');
        $this->response->setOutput($xml->outputMemory());

        unset($xml);
    }

    /**
     * Recursively retrieves categories and appends them as XML elements.
     *
     * This method generates XML elements for each category with a status of 'active'
     * and appends them to the given XMLWriter instance. It includes child categories by
     * calling itself recursively.
     *
     * @param \XMLWriter $xml      The XMLWriter instance used to write the XML structure.
     * @param string     $language The language code to use in the URL link for each category.
     * @param int        $parent_id The ID of the parent category, used for retrieving child categories.
     *
     * @return void
     */
    protected function getCategories(\XMLWriter &$xml, bool $sitemap_category_images, string $language, int $parent_id, array $parent_path = []): void
    {
        $categories = $this->model_extension_ps_google_sitemap_feed_ps_google_sitemap->getCategories($parent_id);

        foreach ($categories as $category) {
            $xml->startElement('url');

            $category_path = array_merge($parent_path, [$category['category_id']]);

            $category_url = $this->url->link('product/category', 'language=' . $language . '&path=' . implode('_', $category_path));

            $xml->writeElement('loc', str_replace('&amp;', '&', $category_url));

            if (isset($category['date_modified'])) {
                $xml->writeElement('lastmod', date('Y-m-d\TH:i:sP', strtotime($category['date_modified'])));
            }

            if ($sitemap_category_images) {
                $resized_image = !empty($category['image']) ? $this->model_tool_image->resize($category['image'], $this->config->get('config_image_popup_width'), $this->config->get('config_image_popup_height')) : null;

                if ($resized_image) {
                    $xml->startElement('image:image');
                    $xml->writeElement('image:loc', $resized_image);
                    $xml->endElement();
                }
            }

            $xml->endElement();

            $this->getCategories($xml, $sitemap_category_images, $language, $category['category_id'], $category_path);
        }
    }
}
