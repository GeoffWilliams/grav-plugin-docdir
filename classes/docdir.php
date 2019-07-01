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
        if (count($relative_route_split) == 0)
        {
            // no version, select the default
            $version = array_keys($versions)[0];
        }
        else
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
        // URL of this doc directory. Make sure to check property exists first
        // or script will crash hard if `error_reporting=E_ALL`
        $docdir = property_exists($page->header(), 'docdir') ?
            $page->header()->docdir : false;

        $query = $docdir ?
            // top down search for versions ( URI -> /xx/foo)
            ['@page.children' => $docdir]:
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
            $this->current_version = $this->extract_version($this->grav['uri']->route(), $docdir, $versions);
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


}
