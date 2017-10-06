<?php
namespace FactionsPro\database;

use FactionsPro\FactionMain;
use FactionsPro\objects\Faction;

abstract class DataProvider {
    /** @var Faction[] $cache */
    private $cache = [];
    /** @var int $cacheSize */
    private $cacheSize;
    /** @var FactionMain $plugin */
    protected $plugin;

    public function __construct(FactionMain $plugin, int $cacheSize = 0) {
        $this->plugin = $plugin;
        $this->cacheSize = $cacheSize;
    }

    /**
     * @param Faction $fac
     */
    protected final function cacheFac(Faction $fac) {
        if ($this->cacheSize > 0) {
            $key = $fac->getName();
            if (isset($this->cache[$key])) {
                unset($this->cache[$key]);
            } elseif($this->cacheSize <= count($this->cache)) {
                array_pop($this->cache);
            }
            $this->cache = array_merge(array($key => clone $fac), $this->cache);
            $this->plugin->getLogger()->debug("{$fac->getName()} has been cached");
        }
    }

    /**
     * @param string $facName
     * @return Faction|null
     */
    protected final function getFacFromCache(string $facName) {
        if ($this->cacheSize > 0) {
            $key = $facName;
            if (isset($this->cache[$key])) {
                $this->plugin->getLogger()->debug("{$facName} was loaded from the cache");
                return $this->cache[$key];
            }
        }
        return null;
    }


    protected final function removeFacFromCache(string $facName) {
        $key = $facName;
        if (isset($this->cache[$key])) {
            $this->plugin->getLogger()->debug("{$facName} was removed from the cache");
            unset($this->cache[$key]);
            if(isset($this->cache[$key])) {
                return true;
            }
            return false;
        }else{
            return true;
        }
    }

    /**
     * @param Faction $fac
     * @return bool
     */
    public abstract function saveFac(Faction $fac) : bool;

    /**
     * @param Faction $fac
     * @return bool
     */
    public abstract function deleteFac(Faction $fac) : bool;

    /**
     * @param string $facName
     * @return Faction|null
     */
    public abstract function getFac(string $facName);

    public abstract function close();
}