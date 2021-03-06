<?php

namespace Voyager\UI\View;

use Voyager\Facade\Request;
use Voyager\Facade\Str;
use Voyager\Resource\Locale\Lang;
use Voyager\Util\Arr;

class ScriptResourceProvider
{
    /**
     * Store all javascript interfaces.
     * 
     * @var \Voyager\Util\Arr
     */

    private static $script;

    /**
     * Provide interfacing from PHP to javascript.
     * 
     * @return  void
     */

    public static function init()
    {
        static::$script = new Arr();
        static::addRequestData();
        static::addDotenvData();
        static::addLocaleData();
        static::provideScripts();
    }

    /**
     * Inject the registered javascript to compiled javascript.
     * 
     * @return  void
     */

    private static function provideScripts() {
        foreach(static::$script->reverse()->get() as $key => $script)
        {
            TemplateEngine::addScript($key, $script);
        }
    }

    /**
     * Register javascript code to the engine.
     * 
     * @param   string $script
     * @return  void
     */

    private static function addScript(string $script)
    {
        static::$script->set(Str::random(10), $script);
    }

    /**
     * Provide some .env data in to javascript.
     * 
     * @return  void
     */

    private static function addDotenvData()
    {
        static::addScript('voyager.setProperty(\'version\',\'' . app()->version() . '\');');
        static::addScript('voyager.setProperty(\'base_url\',\'' . env('APP_URL') . '\');');
        static::addScript('voyager.setProperty(\'locale\',\'' . env('APP_LOCALE') . '\');');
        static::addScript('voyager.setProperty(\'app_name\',\'' . env('INFO_NAME') . '\');');
        static::addScript('voyager.setProperty(\'app_description\',\'' . env('INFO_DESCRIPTION') . '\');');
        static::addScript('voyager.setProperty(\'app_version\',\'' . env('INFO_VERSION') . '\');');
    }

    /**
     * Provide informations about the request.
     * 
     * @return  void
     */

    private static function addRequestData()
    {
        static::addScript('voyager.setProperty(\'current_url\',\'' . addslashes(Request::url()) . '\');');
        static::addScript('voyager.setProperty(\'uri\',\'' . Request::uri() . '\');');
        static::addScript('voyager.setProperty(\'https\',' . (Request::https() ? 'true' : 'false') . ');');
    }

    /**
     * Provide the locale data in to javascript.
     * 
     * @return  void
     */

    private static function addLocaleData()
    {
        $data = [];
        $locale = Lang::load()->get();
        
        foreach($locale as $group_id => $group)
        {
            $items = [];

            foreach($group as $key => $item)
            {
                $items[str_replace('.', '_', $key)] = $item;
            }

            $data[$group_id] = $items;
        }

        static::addScript('voyager.setProperty(\'translations\', JSON.parse("' . addslashes(json_encode($data)) . '"));');
    }

}