<?php
/**
 * Sftp.php
 *
 * Copyright (c) 2012 Silverpop Systems, Inc.
 * http://www.silverpop.com Silverpop Systems, Inc.
 *
 * @author Jerry Ablan <jablan@silverpop.com>
 * @filesource
 */
namespace CIS\Services\Delivery;

/**
 * Sftp
 *
 * Settings used:
 *
 * string        $hostName
 * int           $hostPort
 * string        $userName (optional if keys are used)
 * string        $password (optional if keys are used)
 * string        $publicKey
 * string        $privateKey
 * string        $sourcePath
 * string        $sourceFileName
 * string        $destinationPath
 * string        $destinationFileName
 */
class Sftp extends \CIS\Services\BaseDeliveryService
{
	//**************************************************************************
	//* Public Methods
	//**************************************************************************

	/**
	 * {@InheritDoc}
	 */
	public function deliver( $settings = null )
	{
		$_result = false;
		$_output = null;

		$_settings = parent::deliver( $settings );

		//	Provided by TrexSchedule when dispatching this delivery
		$_sourceFile
			= rtrim( $_settings->sourcePath, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR .
			  $_settings->sourceFileName;

		$_destFile
			= rtrim( $_settings->destinationPath, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR .
			  ( $this->_buildDestinationFileName( $_settings ) ? : $_settings->sourceFileName );

		$this->logDebug( '>>SFTP Delivery Service' );

		try
		{
			$this->logDebug( 'SFTP Deliver sourceFile: ' . $_sourceFile );
			$this->logDebug( '               destFile: ' . $_destFile );

			$_command
				= '/usr/local/bin/sp_sftp' .
				  ' -u ' . escapeshellarg( $_settings->userName ) .
				  ' -p ' . escapeshellarg( $_settings->password ) .
				  ' -h ' . escapeshellarg( $_settings->hostName ) .
				  ' -o ' . ( $_settings->hostPort ? : 22 ) .
				  ' --put ' . escapeshellarg( $_sourceFile ) . ' ' . escapeshellarg( $_destFile );

			$this->logDebug( '                Command: ' . $_command );

			exec( $_command, $_output, $_result );

			if ( 0 != $_result )
			{
				throw new \CIS\Exceptions\DeliveryException( 'Error transferring file to target server: ' . PHP_EOL . print_r( $_output, true ) );
			}

		}
		catch ( \CIS\Exceptions\DeliveryException $_ex )
		{
			$this->logError( 'Delivery exception: ' . $_ex->getMessage() );
		}

		$this->logInfo( '<<SFTP Delivery Service > ' . ( 0 == $_result ? 'Success' : 'Fail' ) );
		return $_result;
	}

}