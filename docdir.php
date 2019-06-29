<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;
use Grav\Common\Uri;

/**
 * Class DocdirPlugin
 * @package Grav\Plugin
 */
class DocdirPlugin extends Plugin
{

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        // Enable the main event we are interested in
        $this->enable([
            'onPageContentRaw' => ['onPageContentRaw', 0]
        ]);
    }

    /**
     * Do some work for this event, full details of events can be found
     * on the learn site: http://learn.getgrav.org/plugins/event-hooks
     *
     * @param Event $e
     */
    public function onPageContentRaw(Event $e)
    {
        $logger = $this->grav['log'];
        $logger->addError("this bit works");
        // Get a variable from the plugin configuration
        //$text = $this->grav['config']->get('plugins.docdir.text_var');


//        $uri = $this->grav['uri'];
//        $version = $uri->query('version');
//        $path = $uri->path();
//
//        $route = $uri->route();
//        $x = implode('|',$this->find_versions($e['page']->path()));
//
//        $text = <<<END
//        <pre>
//        $version
//        $path
//        $route
//        $x
//        </pre>
//END;
//
//
//        // Get the current raw content
//        $content = $e['page']->getRawContent();
//
//        // Prepend the output with the custom text and set back on the page
//        $e['page']->setRawContent($text . "\n\n" . $content);
//
//
        //die("shes dead jim");
    }

    /**
     * Add current directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        $logger = $this->grav['log'];
        $logger->addError("heelo my cunts");

        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';

    }


    /**
     * Set needed variables to display breadcrumbs.
     */
    public function onTwigSiteVariables()
    {

        require_once __DIR__ . '/classes/docdir.php';
        $this->grav['twig']->twig_vars['docdir'] = new Docdir($this->config->get('plugins.docdir'));
    }
}
