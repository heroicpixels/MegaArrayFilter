<?php 

namespace Heroicpixels\MegaArrayFilter;

class MegaArrayFilter {

	protected $filtered = array();
	protected $filters = array();
	protected $globalMutator;
	protected $unfiltered = array();
	
	/**
	 *	Provide an array of valid keys and values
	 */
	public function __construct(array $unfiltered) {
		$this->unfiltered = $unfiltered;
	}
	/**
	 *	Add a filter
	 */
	public function add($key, $valid, $mutator = false, $default = false) {
		if ( !is_array($valid) ) {
			return false;
		}
		$this->filters[$key] = array(
			'valid'		=> $valid,
			'mutator'	=> $mutator,
			'default'	=> $default,
		);
		return $this;
	}
	/**
	 *	Define a global mutator, which can be a function 
	 *	name - e.g. strtolower - or a callback.
	 */
	public function globalMutator($m) {
		$this->globalMutator = $m;
		return $this;
	}
	/**
	 *	Mutate values
	 */
	public function mutate($func, $values) {
		if ( is_callable($func) ) {
			$isFunctionName = is_string($func) && function_exists($func);
			if ( is_array($values) && $isFunctionName ) {
				$values = array_map($func, $values);
			} else {
				$values = $func($values);
			}
		}
		return $values;
	}
	/**
	 *	Filter array
	 */
	public function filter($key = false){
		foreach ( $this->filters as $k => $v ) {
			if ( !isset($this->unfiltered[$k]) ) {
				if ( $v['default'] !== NULL ) {
					$this->filtered[$k] = $v['default'];
				}
				continue;
			}
			$raw = $this->unfiltered[$k];
			$values = NULL;
			if ( $this->globalMutator ) {
				$values = $this->mutate($this->globalMutator, $raw);
			}
			if ( $v['mutator'] ) {
				$values = $this->mutate($v['mutator'], ( isset($values) ? $values : $raw ));
			}
			if ( !isset($values) ) {
				$values = $raw;
			}
			if ( !is_array($v['valid']) ) {
				$v['valid'] = array($v['valid']);
			}
			if ( !is_array($values) ) {
				$this->filtered[$k] = in_array($values, $v['valid']) ? $values : false;
			} else {
				$this->filtered[$k] = array_intersect($v['valid'], $values);
			}
			if ( $key && $key == $k ) {
				return $this->filtered[$k];
			}
		}
		return $this->filtered;
	}

}
