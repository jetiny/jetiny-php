<?php
namespace Jetiny\Base;

class Event {
	
	static public function on($event, $cb) {
		if ($cb) {
			$r = & static::$_events[$event];
			$r[] = $cb;		
		}
	}
	
	static public function off($event, $cb) {
        if (!isset(static::$_events[$event]))
            return;
		if ($r = isset(static::$_events[$event]) ) {
			for($i = count($r)-1; $i>=0 ; --$i) {
				if ($r[$i] == $cb) {
					array_splice(static::$_events[$event], $i);
				}
			}
		}
	}
	
	static public function emit($event, $args = []) {
        if (!isset(static::$_events[$event]))
            return;
		if ($r = static::$_events[$event]) {
			foreach ($r as $k => $v) {
				call_user_func_array($v, $args);
			}
		}
	}
	
	static public function call($event) {
        if (!isset(static::$_events[$event]))
            return;
		if ($r = static::$_events[$event]) {
			$args = func_get_args();
			array_shift($args);
			foreach ($r as $k => $v) {
				call_user_func_array($v, $args);
			}
		}
	}
	
	static public function once($event, $cb) {
		$fn = function() use(&$event, &$cb, &$fn){
			Event::off($event, $fn);
			call_user_func_array($cb, func_get_args());
		};
		Event::on($event, $fn);
	}
	
	static public function keys() {
		return array_keys(static::$_events);
	}
	
	static private $_events = [];
}

