<?php
/**
*
* @package phpBB Extension - Archcry Radio
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	// Main page
	'STATION'					=> 'DJ actual',
	'SONG'						=> 'Canción actual',
	'GENRE'						=> 'Género',
	'BITRATE'					=> 'Bitrate',
	
	// Json
	'RADIO_NOT_AVAILABLE' 		=> 'No disponible',
	
	// ACP languages variables
	'ACP_RADIO_GENERAL'			=> 'Ajustes generales',
	'ACP_RADIO_TITLE'			=> 'Módulo de Radio',
	'ACP_RADIO'					=> 'Ajustes',
	'ACP_RADIO_HOST'			=> 'Host de SHOUTcast',
	'ACP_RADIO_USERAGENT'		=> 'Agente usuario',
	'ACP_RADIO_PORT'			=> 'Puerto',
	'ACP_RADIO_USER'			=> 'Usuario',
	'ACP_RADIO_PASSWD'			=> 'Contraseña',
	'ACP_RADIO_SETTING_SAVED'	=> 'Ajustes de SHOUTcast guardados',
	
	// Links to files for music players
	'ACP_RADIO_MUSIC_PLAYERS'	=> 'Enlaces para los reproductores de música',
	'ACP_RADIO_WINAMP_URL'		=> 'Enlace a Winamp',
	'ACP_RADIO_WMP_URL'			=> 'Enlace a Windows Media Player',
	'ACP_RADIO_REAL_URL'		=> 'Enlace a RealPlayer',
	'ACP_RADIO_ITUNES_URL'		=> 'Enlace a Itunes'
));
