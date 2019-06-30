<?php
namespace Grav\Plugin;

use Grav\Common\Grav;

class Docdir
{

    /**
     * @var array
     */

    protected $config;
    protected $grav;

    protected $current_version;
    protected $versions;

    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->grav = Grav::instance();
        $this->config = $config;
        $this->versions = $this->get_versions();
    }

    /**
     * Given the current route and optional docdir, extract the version, eg:
     * | route                    | docdir   | version |
     * | ---                      | ---      | ---     |
     * | /foo/bar/2_2_2           | null     | 2_2_2   |
     * | /foo/bar/2_2_2/something | /foo/bar | 2_2_2   |
     * @param route $
     * @param Docdir $
     * @return the extracted version
     */
    private function extract_version($route, $docdir, $versions)
    {
        // remove the route - we now have either nothing so we should use the
        // default version, or we have a path inside the docdir so our version
        // is the first element
        $relative_route = str_replace($docdir, "", $route);
        $relative_route_split = explode("/", $relative_route);

        // first element is garbage in all cases
        array_shift($relative_route_split);
        $logger = $this->grav['log'];
        $logger->addError("relroute x" . $relative_route . "x");
        $logger->addError("docdir" . $docdir);
        $logger->addError(count($relative_route_split));
        $logger->addError("x" . $relative_route_split[0] . "x");
        if (count($relative_route_split) == 0)
        {
            // no version, select the default
            $logger->addError("set default version to " . array_keys($versions)[0]);
            $version = array_keys($versions)[0];
        }
        else  //if (count($relative_route_split) > 1)
        {
            $version = $relative_route_split[0];
        }
        return $version;
    }

    /**
     * Scan the directories under this page for `version` directories. These
     * are semantic version numbers with the periods replaced with
     * underscores - eg 1_4_0, 2_0_0, etc.
     * @return Map of version directories with the key as the version name and
     *  the value indicating if it was selected (true)
     */
    public function get_versions()
    {
        $versions = array();

        $page = $this->grav['page'];

        // docdir is the field set in frontmatter that indicates the top level
        // URL of this doc directory
        $docdir = $page->header()->docdir;

        $query = $docdir ?
            // top down search for versions ( URI -> /xx/foo)
            ['@page.children' => $page->header()->docdir]:
            // bottom up search for versions ( URI -> /xx/foo/1_2_3/something)
            ['@self.children' => ''];

        $collection = $page->evaluate($query);
        $ordered_collection = $collection->order('folder', 'desc');

        foreach ($ordered_collection as $e)
        {
            $folder = $e->folder();
            $versions[$folder] = false;
        }

        if (count($versions))
        {
            // pick the first defined:
            // * version (GET parameter)
            // * the newest version (selected earlier)
            $this->current_version = $this->extract_version($this->grav['uri']->route(), $page->header()->docdir, $versions);
            //$this->current_version = $grav['uri']->query('version') ?? array_keys($versions)[0];
            $versions[$this->current_version] = true;
        }

        return $versions;
    }

    public function get_index()
    {
        $page = $this->grav['page'];
        // depending how we were accessed we may already have the version in the
        // URI, eg /foo/2_0_0 vs /foo
        $docdir_root = (strpos($page->url(), $this->current_version) !== false) ?
            $page->url() : $page->url() . "/" . $this->current_version;

        $query = ['@page.children' => $docdir_root];

        $collection = $page->evaluate($query);
        $ordered_collection = $collection->order('folder', 'asc');

        return $ordered_collection->first();

    }


    public function get_menu()
    {
        $page = $this->grav['page'];

        // where does this docsite directory tree begin? if we have docsite
        // (from frontmatter) then we must be a child node so scan from that
        // point, otherwise look at the requested version number for the pages
        // below where we are accessed from (top level request)

        $docdir_root = ($page->header()->docdir ?? $page->url())
            . "/" . $this->current_version;


        $query = ['@page.children' => $docdir_root];
        // example of how to do logging
//        $logger = $this->grav['log'];
//        $logger->addError("requested XXX" . $docdir_root );

        $collection = $page->evaluate($query);
        $ordered_collection = $collection->order('folder', 'asc');

        return $ordered_collection;
    }

    /**
     * Check if the requested version is the one that is currently selected
     * @param $version The version to test
     */
    public function is_selected_version($version)
    {

        $logger = $this->grav['log'];
        $logger->addError("requested:" . $version);
        $current_version = Grav::instance()['uri']->version ?? $this->default_version;
        return $version == $current_version;
    }

    /**
     * Return an array of breadcrumbs for the current page.
     *
     * @return array An array of breadcrumbs.
     */
    public function get()
    {
        // If the breadcrumbs have not yet been generated...
        if (!$this->breadcrumbs) {
            // Generate them now.
            $this->build();
        }

        return $this->breadcrumbs;
    }

    /**
     * Build the array of breadcrumbs.
     *
     * The array is generated by starting at the current page and then climbing
     * the hierarchy until the root is reached. The resulting crumbs are then
     * stored in the $breadcrumbs instance variable.
     *
     * @internal
     */
    protected function build()
    {
        // Used to hold the breadcrumbs as they are being generated.
        $hierarchy = array();

        $current = $this->grav['page'];

        // If the page is not routable...
        if (!$current) {
            // Set up an empty array of crumbs.
            $this->breadcrumbs = array();
            return;
        }

        // If we are not at the root page...
        if (!$current->root()) {

            // If we are configured to include the current page...
            if ($this->config['include_current']) {
                // Place the current page in the hierarchy.
                $hierarchy[$current->url()] = $current;
            }

            $current = $current->parent();

            // As long as $current does not contain the root page...
            while ($current && !$current->root()) {
                // Get the frontmatter of the page.
                $header = $current->header();

                // Assume we may descend unless otherwise told.
                $may_descend = true;

                // If the frontmatter contains a value for $may_descend...
                if(isset(
                    $header->breadcrumbs,
                    $header->breadcrumbs['may_descend']
                )) {
                    // Get that value.
                    $may_descend = $header->breadcrumbs['may_descend'];
                }

                // Then, if breadcrumbs should stop at this page...
                if ($may_descend === false) {
                    // Empty the $hierarchy.
                    $hierarchy = [];
                }

                // Place the current page in the hierarchy.
                $hierarchy[$current->url()] = $current;

                // Get the parent of the current page.
                $current = $current->parent();
            }
        }

        // If we are configured to include the home page...
        if ($this->config['include_home']) {
            // Get the home page.
            $home = $this->grav['pages']->dispatch('/');

            // If the home page isn't already in the hierarchy...
            if ($home && !array_key_exists($home->url(), $hierarchy)) {
                // Place the home page in the hierarchy.
                $hierarchy[] = $home;
            }
        }

        // Reverse the array of breadcrumbs, so that they are in descending
        // order.
        $this->breadcrumbs = array_reverse($hierarchy);
    }
}
