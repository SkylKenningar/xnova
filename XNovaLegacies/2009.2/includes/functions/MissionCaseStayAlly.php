<?php
/**
 * Tis file is part of XNova:Legacies
 *
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @see http://www.xnova-ng.org/
 *
 * Copyright (c) 2009-Present, XNova Support Team <http://www.xnova-ng.org>
 * All rights reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *                                --> NOTICE <--
 *  This file is part of the core development branch, changing its contents will
 * make you unable to use the automatic updates manager. Please refer to the
 * documentation for further information about customizing XNova.
 *
 */

function MissionCaseStayAlly ( $FleetRow ) {
	global $lang;

	$QryStartPlanet   = "SELECT * FROM {{table}} ";
	$QryStartPlanet  .= "WHERE ";
	$QryStartPlanet  .= "`galaxy` = '". $FleetRow['fleet_start_galaxy'] ."' AND ";
	$QryStartPlanet  .= "`system` = '". $FleetRow['fleet_start_system'] ."' AND ";
	$QryStartPlanet  .= "`planet` = '". $FleetRow['fleet_start_planet'] ."';";
	$StartPlanet      = doquery( $QryStartPlanet, 'planets', true);
	$StartName        = $StartPlanet['name'];
	$StartOwner       = $StartPlanet['id_owner'];

	$QryTargetPlanet  = "SELECT * FROM {{table}} ";
	$QryTargetPlanet .= "WHERE ";
	$QryTargetPlanet .= "`galaxy` = '". $FleetRow['fleet_end_galaxy'] ."' AND ";
	$QryTargetPlanet .= "`system` = '". $FleetRow['fleet_end_system'] ."' AND ";
	$QryTargetPlanet .= "`planet` = '". $FleetRow['fleet_end_planet'] ."';";
	$TargetPlanet     = doquery( $QryTargetPlanet, 'planets', true);
	$TargetName       = $TargetPlanet['name'];
	$TargetOwner      = $TargetPlanet['id_owner'];

	if ($FleetRow['fleet_mess'] == 0) {
		if ($FleetRow['fleet_start_time'] <= time()) {

			$Message         = sprintf( $lang['sys_tran_mess_owner'],
									$TargetName, GetTargetAdressLink($FleetRow, ''),
									$FleetRow['fleet_resource_metal'], $lang['Metal'],
									$FleetRow['fleet_resource_crystal'], $lang['Crystal'],
									$FleetRow['fleet_resource_deuterium'], $lang['Deuterium'] );

			SendSimpleMessage ( $StartOwner, '', $FleetRow['fleet_start_time'], 5, $lang['sys_mess_tower'], $lang['sys_mess_transport'], $Message);

			$Message         = sprintf( $lang['sys_tran_mess_user'],
									$StartName, GetStartAdressLink($FleetRow, ''),
									$TargetName, GetTargetAdressLink($FleetRow, ''),
									$FleetRow['fleet_resource_metal'], $lang['Metal'],
									$FleetRow['fleet_resource_crystal'], $lang['Crystal'],
									$FleetRow['fleet_resource_deuterium'], $lang['Deuterium'] );
			SendSimpleMessage ( $TargetOwner, '', $FleetRow['fleet_start_time'], 5, $lang['sys_mess_tower'], $lang['sys_mess_transport'], $Message);
		} elseif ( $FleetRow['fleet_end_stay'] <= time() ) {
			$QryUpdateFleet  = "UPDATE {{table}} SET ";
			$QryUpdateFleet .= "`fleet_mess` = 2 ";
			$QryUpdateFleet .= "WHERE `fleet_id` = '". $FleetRow['fleet_id'] ."' ";
			$QryUpdateFleet .= "LIMIT 1 ;";
			doquery( $QryUpdateFleet, 'fleets');
		}
	} else {
		if ($FleetRow['fleet_end_time'] < time()) {
			$Message         = sprintf ($lang['sys_tran_mess_back'],
									$StartName, GetStartAdressLink($FleetRow, ''));
			SendSimpleMessage ( $StartOwner, '', $FleetRow['fleet_end_time'], 5, $lang['sys_mess_tower'], $lang['sys_mess_fleetback'], $Message);
			RestoreFleetToPlanet ( $FleetRow, true );
			doquery("DELETE FROM {{table}} WHERE fleet_id=" . $FleetRow["fleet_id"], 'fleets');
		}
	}
}

?>