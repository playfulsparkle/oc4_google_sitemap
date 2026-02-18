<?php
namespace Opencart\Admin\Controller\Extension\PSGoogleSitemap\Feed;
/**
 * Class PSGoogleSitemap
 *
 * @package Opencart\Admin\Controller\Extension\PSGoogleSitemap\Feed
 */
class PSGoogleSitemap extends \Opencart\System\Engine\Controller
{
    /**
     * @var string The support email address.
     */
    const EXTENSION_EMAIL = 'support@playfulsparkle.com';

    /**
     * @var string The documentation URL for the extension.
     */
    const EXTENSION_DOC = 'https://github.com/playfulsparkle/oc4_google_sitemap.git';

    /**
     * Displays the Google Sitemap settings page.
     *
     * This method loads the necessary language file, sets the title of the page,
     * and prepares the data for the view. It also generates the breadcrumbs for
     * navigation and retrieves configuration settings for the sitemap.
     *
     * @return void
     */
    public function index(): void
    {
        $this->load->language('extension/ps_google_sitemap/feed/ps_google_sitemap');


        if (isset($this->request->get['store_id'])) {
            $store_id = (int) $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }


        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/ps_google_sitemap/feed/ps_google_sitemap', 'user_token=' . $this->session->data['user_token'])
        ];

        $separator = version_compare(VERSION, '4.0.2.0', '>=') ? '.' : '|';

        $data['action'] = $this->url->link('extension/ps_google_sitemap/feed/ps_google_sitemap' . $separator . 'save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed');

        $data['user_token'] = $this->session->data['user_token'];

        $data['oc4_separator'] = $separator;

        $this->load->model('setting/setting');

        $config = $this->model_setting_setting->getSetting('feed_ps_google_sitemap', $store_id);

        $data['feed_ps_google_sitemap_status'] = isset($config['feed_ps_google_sitemap_status']) ? (bool) $config['feed_ps_google_sitemap_status'] : false;
        $data['feed_ps_google_sitemap_product'] = isset($config['feed_ps_google_sitemap_product']) ? (bool) $config['feed_ps_google_sitemap_product'] : false;
        $data['feed_ps_google_sitemap_product_images'] = isset($config['feed_ps_google_sitemap_product_images']) ? (bool) $config['feed_ps_google_sitemap_product_images'] : false;
        $data['feed_ps_google_sitemap_max_product_images'] = isset($config['feed_ps_google_sitemap_max_product_images']) ? (int) $config['feed_ps_google_sitemap_max_product_images'] : 1;
        $data['feed_ps_google_sitemap_category'] = isset($config['feed_ps_google_sitemap_category']) ? (bool) $config['feed_ps_google_sitemap_category'] : false;
        $data['feed_ps_google_sitemap_category_images'] = isset($config['feed_ps_google_sitemap_category_images']) ? (bool) $config['feed_ps_google_sitemap_category_images'] : false;
        $data['feed_ps_google_sitemap_manufacturer'] = isset($config['feed_ps_google_sitemap_manufacturer']) ? (bool) $config['feed_ps_google_sitemap_manufacturer'] : false;
        $data['feed_ps_google_sitemap_manufacturer_images'] = isset($config['feed_ps_google_sitemap_manufacturer_images']) ? (bool) $config['feed_ps_google_sitemap_manufacturer_images'] : false;
        $data['feed_ps_google_sitemap_information'] = isset($config['feed_ps_google_sitemap_information']) ? (bool) $config['feed_ps_google_sitemap_information'] : false;
        $data['feed_ps_google_sitemap_topic'] = isset($config['feed_ps_google_sitemap_topic']) ? (bool) $config['feed_ps_google_sitemap_topic'] : false;
        $data['feed_ps_google_sitemap_article'] = isset($config['feed_ps_google_sitemap_article']) ? (bool) $config['feed_ps_google_sitemap_article'] : false;

        $data['is_oc_4_1'] = version_compare(VERSION, '4.1.0.0', '>=');

        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();

        $data['languages'] = $languages;

        $data['store_id'] = $store_id;

        $data['stores'] = [];

        $data['stores'][] = [
            'store_id' => 0,
            'name' => $this->config->get('config_name') . '&nbsp;' . $this->language->get('text_default'),
            'href' => $this->url->link('extension/ps_google_sitemap/feed/ps_google_sitemap', 'user_token=' . $this->session->data['user_token'] . '&store_id=0'),
        ];

        $this->load->model('setting/store');

        $stores = $this->model_setting_store->getStores();

        $store_url = HTTP_CATALOG;

        foreach ($stores as $store) {
            $data['stores'][] = [
                'store_id' => $store['store_id'],
                'name' => $store['name'],
                'href' => $this->url->link('extension/ps_google_sitemap/feed/ps_google_sitemap', 'user_token=' . $this->session->data['user_token'] . '&store_id=' . $store['store_id']),
            ];

            if ((int) $store['store_id'] === $store_id) {
                $store_url = $store['url'];
            }
        }

        $data['data_feed_seo_urls'] = [];
        $data['data_feed_urls'] = [];

        $htaccess_mod = [];

        foreach ($languages as $language) {
            $feed_seo_url = rtrim($store_url, '/') . '/' . $language['code'] . '/sitemap.xml';
            $feed_url = rtrim($store_url, '/') . '/index.php?route=extension/ps_google_sitemap/feed/ps_google_sitemap&language=' . $language['code'];

            $data['data_feed_seo_urls'][$language['language_id']] = $feed_seo_url;
            $data['data_feed_urls'][$language['language_id']] = $feed_url;

            $htaccess_mod[] = 'RewriteRule ^' . $language['code'] . '/sitemap.xml$ index.php?route=extension/ps_google_sitemap/feed/ps_google_sitemap&language=' . $language['code'] . ' [L]';
        }

        $data['htaccess_mod'] = implode(PHP_EOL, $htaccess_mod);

        $data['user_agents'] = array(
            '*' => $this->language->get('text_user_agent_any'),
            'Googlebot' => 'Googlebot',
            'Googlebot-Image' => 'Googlebot Image',
            'Googlebot-Video' => 'Googlebot Video',
            'Googlebot-News' => 'Googlebot News',
            'Bingbot' => 'Bingbot',
            'msnbot' => 'MSNBot',
            'YandexBot' => 'YandexBot',
            'YandexImages' => 'YandexImages',
            'YandexVideo' => 'YandexVideo',
            'YandexMedia' => 'YandexMedia',
            'Baiduspider' => 'BaiduSpider',
            'SeznamBot' => 'SeznamBot',
        );

        $data['config_seo_url'] = $this->config->get('config_seo_url');

        $data['text_contact'] = sprintf($this->language->get('text_contact'), self::EXTENSION_EMAIL, self::EXTENSION_EMAIL, self::EXTENSION_DOC);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/ps_google_sitemap/feed/ps_google_sitemap', $data));
    }

    /**
     * Saves the settings for the Google Sitemap.
     *
     * This method validates user permissions, processes the submitted form data,
     * and saves the settings to the database. It returns a JSON response indicating
     * success or failure.
     *
     * @return void
     */
    public function save(): void
    {
        $this->load->language('extension/ps_google_sitemap/feed/ps_google_sitemap');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/ps_google_sitemap/feed/ps_google_sitemap')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json && !isset($this->request->post['store_id'])) {
            $json['error'] = $this->language->get('error_store_id');
        }

        if (!$json && $this->request->post['feed_ps_google_sitemap_max_product_images'] < 0) {
            $json['error']['input-max-product-images'] = $this->language->get('error_max_product_images_min');
        }

        if (!$json) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('feed_ps_google_sitemap', $this->request->post, $this->request->post['store_id']);

            $json['success'] = $this->language->get('text_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    /**
     * Installs the Google Sitemap extension.
     *
     * This method will contain logic to set up the necessary configurations or
     * database tables needed for the extension upon installation. Currently, it is empty.
     *
     * @return void
     */
    public function install(): void
    {
        $this->load->model('setting/setting');

        $data = [
            'feed_ps_google_sitemap_category_images' => 0,
            'feed_ps_google_sitemap_category' => 1,
            'feed_ps_google_sitemap_information' => 1,
            'feed_ps_google_sitemap_manufacturer_images' => 0,
            'feed_ps_google_sitemap_manufacturer' => 1,
            'feed_ps_google_sitemap_max_product_images' => 1,
            'feed_ps_google_sitemap_product_images' => 1,
            'feed_ps_google_sitemap_product' => 1,
            'feed_ps_google_sitemap_status' => 0,
        ];

        if (version_compare(VERSION, '4.1.0.0', '>=')) {
            $data['feed_ps_google_sitemap_topic'] = 1;
            $data['feed_ps_google_sitemap_article'] = 1;
        }

        $this->model_setting_setting->editSetting('feed_ps_google_sitemap', $data);
    }

    /**
     * Uninstalls the Google Sitemap extension.
     *
     * This method will contain logic to remove configurations or database tables
     * created by the extension upon uninstallation. Currently, it is empty.
     *
     * @return void
     */
    public function uninstall(): void
    {

    }

    private function _validateRobotsTxt(string $testUserAgent, array $urls): array
    {
        $results = [];

        $robotsTxt = dirname(DIR_SYSTEM) . '/robots.txt';

        if (is_readable($robotsTxt)) {
            $lines = file($robotsTxt); // Read the robots.txt file lines
        } else {
            $lines = false;
        }

        // If the file is not readable, assume no URLs are blocked
        if (false === $lines) {
            foreach ($urls as $url) {
                $results[$url] = 'text_allowed'; // No blocking when no robots.txt is found
            }
            return $results;
        }

        // Iterate through each URL to check
        foreach ($urls as $url) {
            $parsedUrl = parse_url($url);
            $path = $parsedUrl['path'] . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');

            // Variables to track user-agent and blocking status
            $userAgent = null;
            $isBlocked = false;
            $disallowedPaths = [];

            // Check each line in robots.txt
            foreach ($lines as $line) {
                $line = trim($line);

                // Skip empty lines or comments
                if (empty($line) || $line[0] == '#') {
                    continue;
                }

                // Check if it's a User-agent directive
                if (strpos($line, 'User-agent:') === 0) {
                    $userAgent = trim(substr($line, 11)); // Extract user-agent
                    continue; // Move to the next line
                }

                // If user-agent is test user-agent or wildcard '*', process the Disallow
                if ($userAgent === $testUserAgent || $userAgent === '*' || $userAgent === null) {
                    if (strpos($line, 'Disallow:') === 0) {
                        $disallowedPath = trim(substr($line, 9)); // Extract disallowed path
                        $disallowedPaths[] = $disallowedPath; // Store disallowed paths
                    }
                }
            }

            // Check if any of the disallowed paths match the current URL
            foreach ($disallowedPaths as $disallowedPath) {
                $regexPattern = $this->convertToRegex($disallowedPath);
                if (preg_match($regexPattern, $path)) {
                    $isBlocked = true;
                    break; // Stop checking if the URL is already blocked
                }
            }

            // Store the result for this URL
            $results[$url] = $isBlocked ? 'text_disallowed' : 'text_allowed';
        }

        return $results; // Return the array of results for each URL
    }

    /**
     * Converts a Disallow pattern to a regular expression
     * This function handles basic wildcard conversion like * and $
     *
     * @param string $disallowedPath
     * @return string
     */
    private function convertToRegex($disallowedPath)
    {
        // Escape any regular expression special characters
        $disallowedPath = preg_quote($disallowedPath, '/');

        // Replace wildcard '*' with '.*' to match any number of characters
        $disallowedPath = str_replace('\*', '.*', $disallowedPath);

        // Replace '$' with '\z' to match the end of the string
        $disallowedPath = str_replace('\$', '\z', $disallowedPath);

        // Make sure the regular expression matches the entire path (not just a part of it)
        return '/^' . $disallowedPath . '/';
    }

    public function validaterobotstxt()
    {
        $this->load->language('extension/ps_google_sitemap/feed/ps_google_sitemap');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/ps_google_sitemap/feed/ps_google_sitemap')) {
            $json['error'] = $this->language->get('error_permission');
        }

        $this->load->model('localisation/language');

        if (isset($this->request->get['store_id'])) {
            $store_id = (int) $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }

        if (isset($this->request->get['user_agent'])) {
            $user_agent = $this->request->get['user_agent'];
        } else {
            $user_agent = '*';
        }

        $store_url = HTTP_CATALOG;

        if (!$json) {
            $feed_seo_urls = [];

            $languages = $this->model_localisation_language->getLanguages();

            foreach ($languages as $language) {
                $feed_seo_urls[] = rtrim($store_url, '/') . '/index.php?route=extension/ps_google_sitemap/feed/ps_google_sitemap&language=' . $language['code'];
            }

            if ($this->config->get('config_seo_url')) {
                foreach ($languages as $language) {
                    $feed_seo_urls[] = rtrim($store_url, '/') . '/' . $language['code'] . '/sitemap.xml';
                }
            }

            $results = [];

            $validationResults = $this->_validateRobotsTxt($user_agent, $feed_seo_urls);

            foreach ($validationResults as $feed_seo_url => $translation) {
                $results[] = sprintf($this->language->get($translation), $feed_seo_url);
            }

            $json['results'] = implode(PHP_EOL, $results);
        } else {
            $json['results'] = '';
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function _patchHtaccess()
    {
        $htaccess_filename = dirname(DIR_SYSTEM) . '/.htaccess';

        if (!is_readable($htaccess_filename)) {
            return false;
        }

        $lines = file($htaccess_filename);

        if (false === $lines) {
            return false;
        }

        if (empty($lines)) {
            return true;
        }

        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();

        $rules = [];

        foreach ($languages as $language) {
            $canAddRule = true;

            $rule = 'RewriteRule ^' . $language['code'] . '/sitemap.xml$ index.php?route=extension/ps_google_sitemap/feed/ps_google_sitemap&language=' . $language['code'] . ' [L]';

            foreach ($lines as $line) {
                if (strpos($line, $rule) !== false) {
                    $canAddRule = false;
                }
            }

            if ($canAddRule) {
                $rules[] = $rule;
            }
        }

        $new_content = '';
        $foundRewriteEngine = false;

        foreach ($lines as $line) {
            $new_content .= $line;

            if (!$foundRewriteEngine && strtolower(trim($line)) === 'rewriteengine on') {
                $foundRewriteEngine = true;
                $new_content .= PHP_EOL . PHP_EOL;
                foreach ($rules as $rule) {
                    $new_content .= $rule . PHP_EOL;
                }
            }
        }

        if ($rules && !empty($new_content)) {
            return file_put_contents($htaccess_filename, $new_content) !== false;
        }

        return true;
    }

    public function patchhtaccess()
    {
        $this->load->language('extension/ps_google_sitemap/feed/ps_google_sitemap');

        $json = [];

        if (!$this->user->hasPermission('modify', 'extension/ps_google_sitemap/feed/ps_google_sitemap')) {
            $json['error'] = $this->language->get('error_permission');
        }

        if (!$json) {
            if (!$this->_patchHtaccess()) {
                $json['error'] = $this->language->get('error_htaccess_update');
            } else {
                $json['success'] = $this->language->get('text_htaccess_update_success');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
