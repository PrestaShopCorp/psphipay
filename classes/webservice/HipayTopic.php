<?php
/**
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2014 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_'))
	exit;

require_once(dirname(__FILE__).'/HipayWS.php');

class HipayTopic extends HipayWS
{

	protected $client_url = '/soap/website-topics-v2';

	/* Topics list */
	protected static $topics = array();

	/* SOAP method: codes */
	public function getTopics()
	{
		if (count(self::$topics) === 0)
		{
			$locale = new HipayLocale();
			$business = new HipayBusiness();

			$params = array(
				'locale' => $locale->getLocale(),
				'businessLineId' => $business->getBusinessId(),
			);

			$results = $this->doQuery('get', $params);

			if ($results->getResult->code === 0)
			{
				if (is_array($results->getResult->websiteTopics->item) === false)
					self::$topics[] = $results->getResult->websiteTopics->item;
				else
					self::$topics = (array)$results->getResult->websiteTopics->item;
			}
		}

		return self::$topics;
	}

	public function getTopic()
	{
		$topic = new StdClass();

		$topic->id = $this->getTopicId();
		$topic->label = $this->getTopicLabel();

		return $topic;
	}

	public function getTopicId()
	{
		$topic_id = (int)Configuration::get('PSP_HIPAY_TOPIC_ID');

		if ($topic_id == 0)
		{
			$topics = $this->getTopics();

			if ((is_array($topics) === true) && (count($topics) > 0))
			{
				$default_topic = array_pop($topics);
				Configuration::updateValue('PSP_HIPAY_TOPIC_ID', $default_topic->id);
				Configuration::updateValue('PSP_HIPAY_TOPIC_LABEL', $default_topic->label);
			}
		}

		return (int)Configuration::get('PSP_HIPAY_TOPIC_ID');
	}

	public function getTopicLabel()
	{
		$topic_label = (int)Configuration::get('PSP_HIPAY_TOPIC_LABEL');

		if ($topic_label == 0)
		{
			$topics = $this->getTopics();

			if ((is_array($topics) === true) && (count($topics) > 0))
			{
				$default_topic = array_pop($topics);
				Configuration::updateValue('PSP_HIPAY_TOPIC_ID', $default_topic->id);
				Configuration::updateValue('PSP_HIPAY_TOPIC_LABEL', $default_topic->label);
			}
		}

		return (string)Configuration::get('PSP_HIPAY_TOPIC_LABEL');
	}

}
