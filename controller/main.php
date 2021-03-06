<?php
/**
*
* @package phpBB Extension - Archcry Radio
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace archcry\radio\controller;
use Symfony\Component\HttpFoundation\Response;

class main
{
	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\user */
	protected $user;
	
	/* @var \phpbb\cache\service */
	protected $cache;

	/**
	* Constructor
	*
	* @param \phpbb\config\config		$config
	* @param \phpbb\controller\helper	$helper
	* @param \phpbb\user				$user
	*/
	public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\user $user, \phpbb\cache\service $cache)
	{
		$this->config = $config;
		$this->user = $user;
		$this->helper = $helper;
		$this->cache = $cache;
	}

	/**
	 * @return Response
     */
	public function get()
	{
		$response = new Response();
		$response->headers->set('Content-Type', 'application/json');

		// Check if the radio information is available in our cache
		$radio = $this->checkCache();

		// Set content for the response
		$response->setContent(json_encode($radio));

		// Send the response
		return $response;
	}

	/**
	 * @return array|mixed
     */
	private function getRadioInformation() {
		$ch = $this->initCurl($this->config);

		// connect to shoutcast server
		$curl = curl_exec($ch); 
	
		// now get the xml data 
		if ($curl) 
		{ 
	   		$xml = @simplexml_load_string($curl);

			$dnas_data = $this->extractRadioData($xml);
			$dnas_data = $this->extractListenersAndSongs($xml, $dnas_data);
		} 
		else 
		{ 
		    $dnas_data = array('error' => 'connection error'); 
		} 
		
		return $dnas_data;
	}

	/**
	 * @param $config
	 * @return resource
     */
	private function initCurl($config)
	{
		//init curl connection
		$ch = curl_init($config['archcry_radio_host'] . '/admin.cgi?mode=viewxml&sid=1');

		// set curl connection parameter
		curl_setopt($ch, CURLOPT_PORT, $config['archcry_radio_port']);
		curl_setopt($ch, CURLOPT_USERAGENT, $config['archcry_radio_useragent']);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, $config['archcry_radio_user'] . ':' . $config['archcry_radio_passwd']);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE);

		return $ch;
	}

	/**
	 * @param $xml
	 * @return array
	 */
	private function extractRadioData($xml)
	{
		$dnas_data = array(
			'currentListeners' => (string)(!empty($xml->CURRENTLISTENERS) ? $xml->CURRENTLISTENERS : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'peakListeners' => (string)(!empty($xml->PEAKLISTENERS) ? $xml->PEAKLISTENERS : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'maxListeners' => (string)(!empty($xml->MAXLISTENERS) ? $xml->MAXLISTENERS : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'reportedListeners' => (string)(!empty($xml->REPORTEDLISTENERS) ? $xml->REPORTEDLISTENERS : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'avarageTime' => (string)(!empty($xml->AVERAGETIME) ? $xml->AVERAGETIME : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'serverGenre' => (string)(!empty($xml->SERVERGENRE) ? $xml->SERVERGENRE : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'serverUrl' => (string)(!empty($xml->SERVERURL) ? $xml->SERVERURL : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'serverTitle' => (string)(!empty($xml->SERVERTITLE) ? $xml->SERVERTITLE : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'songTitle' => (string)(!empty($xml->SONGTITLE) ? $xml->SONGTITLE : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'nextTitle' => (string)(!empty($xml->NEXTTITLE) ? $xml->NEXTTITLE : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'songUrl' => (string)(!empty($xml->SONGURL) ? $xml->SONGURL : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'irc' => (string)(!empty($xml->IRC) ? $xml->IRC : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'icq' => (string)(!empty($xml->ICQ) ? $xml->ICQ : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'aim' => (string)(!empty($xml->AIM) ? $xml->AIM : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'streamStatus' => (string)(!empty($xml->STREAMSTATUS) ? $xml->STREAMSTATUS : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'bitrate' => (string)(!empty($xml->BITRATE) ? $xml->BITRATE : $this->user->lang('RADIO_NOT_AVAILABLE')),
			'content' => (string)(!empty($xml->CONTENT) ? $xml->CONTENT : $this->user->lang('RADIO_NOT_AVAILABLE'))
		);

		return $dnas_data;
	}

	/**
	 * @param $xml
	 * @param $dnas_data
	 * @return mixed
	 */
	private function extractListenersAndSongs($xml, $dnas_data)
	{
		if ($xml->STREAMSTATUS == 1) {
			// store song history in array
			foreach ($xml->SONGHISTORY->SONG as $song) {
				$dnas_data['songHistory'][] = array(
					'playeDat' => (string)(!empty($song->PLAYEDAT) ? $song->PLAYEDAT : $this->user->lang('RADIO_NOT_AVAILABLE')),
					'title' => (string)(!empty($song->TITLE) ? $song->TITLE : $this->user->lang('RADIO_NOT_AVAILABLE')),
				);
			}
		}

		return $dnas_data;
	}

	/**
	 * @return array|mixed
	 */
	private function checkCache()
	{
		if (($radio = $this->cache->get('_radio')) === false) {
			// Get radio information
			$radio = $this::getRadioInformation();

			// Save this to the cache to improve performance
			$this->cache->put('_radio', $radio, 30);
			return $radio;
		}

		return $radio;
	}
}
