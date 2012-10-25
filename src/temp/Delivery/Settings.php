<?php
/**
 * Settings.php
 *
 * Copyright (c) 2012 Silverpop Systems, Inc.
 * http://www.silverpop.com Silverpop Systems, Inc.
 *
 * @author Jerry Ablan <jablan@silverpop.com>
 * @filesource
 */
namespace CIS\Services\Delivery;

/**
 * Settings
 * Generic delivery service settings
 *
 * @property int          $deliveryMethod
 * @property array        $reportHeaders
 * @property string|array $recipients
 * @property int          $trexScheduleId
 * @property int          $trexClientId
 *
 * @property string       $hostName
 * @property int          $hostPort
 * @property string       $userName
 * @property string       $password
 * @property string       $publicKey      Public key path
 * @property string       $privateKey     Private key path
 *
 * @property string       $destinationPath
 * @property string       $destinationFileName
 * @property string       $destinationFilePattern
 *
 * @property string       $sourcePath     Source path
 * @property string       $sourceFileName Source file name
 */
class Settings extends \CIS\Services\ServiceSettings
{
	//*************************************************************************
	//* Public Methods
	//*************************************************************************

}