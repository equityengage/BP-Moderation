<?php
/*
Plugin Name: BuddyPress Moderation
Plugin URI: http://buddypress.org/community/groups/bp-moderation/
Description: Plugin for moderation of buddypress communities, it adds a 'report this' link to every content so members can help admin finding inappropriate content.
Version: 0.1.6
Author: Francesco Laffi
Author URI: http://flweb.it
License: GPL2
Network: true
Text Domain: bp-moderation
Domain Path: /lang
*/

/*  Copyright 2011  Francesco Laffi  (email : francesco.laffi@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

namespace BPModeration;

const BASE_FILE = __FILE__;

spl_autoload_register(function ($class) {
    if (__NAMESPACE__.'\\' === substr($class, 0, strlen(__NAMESPACE__)+1)) {
        require __DIR__.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $class).'.php';
    }

    //
    if ('bpMod' ===  substr($class, 0, 5)) {
        require_once __DIR__.'/classes/'.$class.'.php';
    }
});

//load the plugin
new BPModeration();
