{*
    Blacknova Traders - A web-based massively multiplayer space combat and trading game
    Copyright (C) 2025 Simon Dann

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

    File: main.tpl
*}

{extends file="layout.tpl"}

{block name=body}
    {if $variables['messages'] > 0}
        <script>
            alert('{$langvars['l_youhave']} {$variables['messages']} {$langvars['l_messages_wait']}');
        </script>
    {/if}

    <div style='width:90%; margin:auto; background-color:#400040; color:#C0C0C0; text-align:center; border:#fff 1px solid; padding:4px;'>
        {$langvars[$variables['player']['rank']]} <span style='color:#fff; font-weight:bold;'>{$variables['player']['character_name']}</span>{$langvars['l_aboard']} <span style='color:#fff; font-weight:bold;'><a class='new_link' style='font-size:14px;' href='report.php'>{$variables['player']['ship']['name']}</a></span>
    </div>

    <table style='width:90%; margin:auto; text-align:center; border:0px;'>
        <tr>
            <td style='width:33%; text-align:left; color:#ccc; font-size:12px;'>&nbsp;{$langvars['l_turns_have']} <span style='color:#fff; font-weight:bold;'>{$variables['player']['turns']['available']}</span></td>
            <td style='width:33%; text-align:center; color:#ccc; font-size:12px;'>{$langvars['l_turns_used']} <span style='color:#fff; font-weight:bold;'>{$variables['player']['turns']['consumed']}</span></td>
            <td style='width:33%; text-align:right; color:#ccc; font-size:12px;'>{$langvars['l_score']} <span style='color:#fff; font-weight:bold;'><a href='main.php?command=score'>{$variables['player']['score']}</a>&nbsp;</span></td>
        </tr>
        <tr>
            <td colspan='3' style='width:33%; text-align:right; color:#ccc; font-size:12px;'>&nbsp;{$langvars['l_credits']}: <span style='color:#fff; font-weight:bold;'>{$variables['player']['credits']}</span></td>
        </tr>
        <tr>
            <td style='text-align:left; color:#ccc; font-size:12px;'>&nbsp;{$langvars['l_sector']} <span style='color:#fff; font-weight:bold;'>{$variables['player']['ship']['sector']}</span></td>
            <td style='text-align:center; color:#fff; font-size:12px; font-weight:bold;'>&nbsp;{$variables['sector']['beacon']}&nbsp;</td>
            <td style='text-align:right; color:#ccc; font-size:12px; font-weight:bold;'><a class='new_link' href='zoneinfo.php?zone={$variables['sector']['zone']['id']}'>{$variables['sector']['zone']['name']}</a>&nbsp;</td>
        </tr>
    </table>

    <br>

    <table style='width:90%; margin:auto; border:0px; border-spacing:0px;'>
        <tr>
            <!-- Left Side -->
            <td style='width:200px; vertical-align:top; text-align:center;'>
                {if $variables['player']['gravatar']}
                    <table style='width:140px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>
                        <tr style='vertical-align:top'>
                            <td style='padding:0px; width:8px;'><img style='border:0px; height:18px; width:8px; float:right;' src='templates/{$variables['template']}/images/lcorner.png' alt=''></td>
                            <td style='padding:0px; background-color:#400040; text-align:center; vertical-align:middle;'><strong style='font-size:0.75em; color:#fff;'>{$langvars['l_avatar']}</strong></td>
                            <td style='padding:0px; width:8px'><img style='border:0px; height:18px; width:8px; float:left;' src='templates/{$variables['template']}/images/rcorner.png' alt=''></td>
                        </tr>
                    </table>

                    <table style='width:150px; margin:auto; text-align:center; border:0px; padding:0px; border-spacing:0px'>
                        <tr>
                            <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050; width:150px'>
                                <img style='display:block; margin-left:auto; margin-right:auto' height='80' width='80' alt='Player Avatar' src='{$variables['player']['gravatar']}'>
                                <div style='padding-left:4px; text-align:left'>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <br>
                {/if}

                <!-- Menu: Caption -->
                <table style='width:140px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>
                    <tr style='vertical-align:top'>
                        <td style='padding:0px; width:8px;'><img style='border:0px; height:18px; width:8px; float:right;' src='templates/{$variables['template']}/images/lcorner.png' alt=''></td>
                        <td style='padding:0px; white-space:nowrap; background-color:#400040; text-align:center; vertical-align:middle;'><strong style='font-size:0.75em; color:#fff;'>{$langvars['l_commands']}</strong></td>
                        <td style='padding:0px; width:8px'><img style='border:0px; height:18px; width:8px; float:left;' src='templates/{$variables['template']}/images/rcorner.png' alt=''></td>
                    </tr>
                </table>

                <!-- Menu -->
                <table style='width:150px; margin:auto; text-align:center; border:0px; padding:0px; border-spacing:0px'>
                    <tr>
                        <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050;'>
                            {if $variables['player']['is_admin']}
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='admin.php'>{$langvars['l_admin_menu']}</a></div>
                            {/if}

                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='device.php'>{$langvars['l_devices']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='planet_report.php'>{$langvars['l_planets']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='igb.php'>{$langvars['l_igb']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='log.php'>{$langvars['l_log']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='defence_report.php'>{$langvars['l_sector_def']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='readmail.php'>{$langvars['l_read_msg']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='mailto.php'>{$langvars['l_send_msg']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='ranking.php'>{$langvars['l_rankings']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='settings.php'>{$langvars['l_settings']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='teams.php'>{$langvars['l_teams']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='self_destruct.php'>{$langvars['l_ohno']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='options.php'>{$langvars['l_options']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='navcomp.php'>{$langvars['l_navcomp']}</a></div>

                            {if $variables['allow_ksm']}
                                <div style='padding-left:4px; text-align:left;'><a class='mnu' href='galaxy.php'>{$langvars['l_map']}</a></div>
                            {/if}
                        </td>
                    </tr>
                    <tr>
                        <td style='white-space:nowrap; height:2px; background-color:transparent;'></td>
                    </tr>
                    <tr>
                        <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050;'>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='faq.php'>{$langvars['l_faq']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='feedback.php'>{$langvars['l_feedback']}</a></div>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='#'>{$langvars['l_forums']}</a></div>
                        </td>
                    </tr>
                    <tr>
                        <td style='white-space:nowrap; height:2px; background-color:transparent;'></td>
                    </tr>
                    <tr>
                        <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050;'>
                            <div style='padding-left:4px; text-align:left;'><a class='mnu' href='logout.php'>{$langvars['l_logout']}</a></div>
                        </td>
                    </tr>
                </table>

                <br>

                <!-- Trade Routes: Caption -->
                <table style='width:140px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>
                    <tr style='vertical-align:top;'>
                        <td style='padding:0px; width:8px;'><img style='width:8px; height:18px; border:0px; float:right;' src='templates/{$variables['template']}/images/lcorner.png' alt=''></td>
                        <td style='padding:0px; white-space:nowrap; background-color:#400040; text-align:center; vertical-align:middle;'><strong style='font-size:0.75em; color:#fff;'>{$langvars['l_traderoutes']}</strong></td>
                        <td style='padding:0px; width:8px;'><img style='width:8px; height:18px; border:0px; float:left;' src='templates/{$variables['template']}/images/rcorner.png' alt=''></td>
                    </tr>
                </table>

                <!-- Trade Routes -->
                <table style='width:150px; margin:auto; text-align:center; border:0px; padding:0px; border-spacing:0px;'>
                    <tr>
                        <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050;'>
                            {foreach item=route from=$variables['trade_routes']}
                                <div style='text-align:center;'>
                                    &nbsp;<a class=mnu href=traderoute.php?engage={$route['id']}>
                                        {if $route['source_type'] === 'P'}
                                            {$langvars['l_port']}&nbsp;
                                        {elseif $route['source_type'] === 'D'}
                                            Def's&nbsp;
                                        {else}
                                            {$route['source']['name']}
                                        {/if}

                                        {if $route['circuit'] === 1}
                                            =&gt;&nbsp;
                                        {else}
                                            &lt;=&gt;&nbsp;
                                        {/if}

                                        {if $route['destination_type'] === 'P'}
                                            {$route['destination']['id']}
                                        {elseif $route['destination_type'] === 'D'}
                                            Def's in {$route['destination']['id']}
                                        {else}
                                            {$route['destination']['name']}
                                        {/if}
                                    </a>
                                </div>
                            {foreachelse}
                                <div style='text-align:center;'><a class='dis'>&nbsp;{$langvars['l_none']} &nbsp;</a></div>
                            {/foreach}
                        </td>
                    </tr>
                    <tr>
                        <td style='white-space:nowrap; height:2px; background-color:transparent;'></td>
                    </tr>
                    <tr>
                        <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050;'>
                            <div style='padding-left:4px; text-align:center;'><a class='mnu' href='traderoute.php'>{$langvars['l_trade_control']}</a></div>
                        </td>
                    </tr>
                </table>
                <br>
            </td>

            <!-- Middle -->
            <td style='vertical-align:top;'>
                <!-- Trading Port -->

                {if $variables['sector']['port']['type'] !== 'none'}
                    <div style='color:#fff; text-align:center; font-size:14px;'>
                        {$langvars['l_tradingport']}:&nbsp;<span style='color:#0f0;'>{$variables['sector']['port']['name']}</span>
                        <br><br>
                        <a class='new_link' style='font-size:14px;' href='port.php' title='Dock with Space Port'><img style='width:100px; height:70px;' class='mnu' src='templates/{$variables['template']}/images/space_station_port.png' alt='Space Station Port'></a>
                    </div>
                {else}
                    <div style='color:#fff; text-align:center;'>{$langvars['l_tradingport']}&nbsp;{$langvars['l_none']}</div>
                {/if}

                <br>

                <!-- Planets -->

                <div style='margin-left:auto; margin-right:auto; text-align:center; border:transparent 1px solid;'>
                    <div style='text-align:center; font-size:12px; color:#fff; font-weight:bold;'>{$langvars['l_planet_in_sec']} {$variables['sector']['id']}</div>
                    <table style='height:150px; text-align:center; margin:auto; border:0px'>
                        {foreach item=planet from=$variables['sector']['planets']}
                            <tr>
                                <td style='margin-left:auto; margin-right:auto; vertical-align:top; width:79px; height:90px; padding:4px;'>
                                    <a href='planet.php?planet_id={$planet['id']}'>
                                        <img class='mnu' title='Interact with Planet' src="templates/{$variables['template']}/images/{$planet['image']}" style='width:79px; height:90px; border:0' alt="planet">
                                    </a>
                                    <br>
                                    <span style='font-size:10px; color:#fff;'>
                                        {$planet['name']}<br>{$planet['owner_name']}
                                    </span>
                                </td>
                            </tr>
                        {foreachelse}
                        <tr>
                            <td style='margin-left:auto; margin-right:auto; vertical-align:top'>
                                <br><span style='color:white; size:1.25em'>{$langvars['l_none']}</span><br><br>
                            </td>
                        </tr>
                        {/foreach}
                    </table>
                </div>

                <!-- Ships -->
                <div style='text-align:center; border:transparent 1px solid;'>
                    <div style='text-align:center; font-size:12px; color:#fff; font-weight:bold;'>{$langvars['l_ships_in_sec']} {$variables['sector']['id']}</div>
                    {if count($variables['sector']['ships_detected']) === 0}
                        <div style='color:#fff;'>{$langvars['l_none']}</div>
                    {else}
                        <div style='padding-top:4px; padding-bottom:4px; width:500px; margin:auto; background-color:#303030;'>{$langvars['l_main_ships_detected']}</div>
                        <div style='width:498px; margin:auto; overflow:auto; height:145px; scrollbar-base-color: #303030; scrollbar-arrow-color: #fff; padding:0px;'>
                            <table style='padding:0px; border-spacing:1px;'>
                                <tr>
                                    {foreach item=ship from=$variables['sector']['ships_detected']}
                                    <td style='text-align:center; vertical-align:top; padding:1px;'>
                                        <div style='width:160px; height:120px; background: url(templates/{$variables['template']}/images/bg_alpha.png) repeat; padding:1px;'>
                                            <a href=ship.php?ship_id={$ship['ship_id']}>
                                                <img class='mnu' title='Interact with Ship' src="templates/{$variables['template']}/images/{$ship['ship']['image']}" style='width:80px; height:60px; border:0px'>
                                            </a>
                                            <div style='font-size:12px; color:#fff; white-space:nowrap;'>{$ship['ship']['name']}<br>
                                                (<span style='color:#ff0; white-space:nowrap;'>{$ship['character_name']}</span>)<br>

                                            </div>
                                        </div>
                                    </td>
                                    {/foreach}
                                </tr>
                            </table>
                        </div>
                    {/if}
                </div>

                <!-- Sector Defense -->
                {if count($variables['sector']['defences']) > 0}
                <br>
                <div style='padding-top:4px; padding-bottom:4px; width:500px; margin:auto; background-color:#303030; text-align:center;'>{$langvars['l_sector_def']}</div>
                <div style='width:498px; margin:auto; overflow:auto; height:125px; scrollbar-base-color: #303030; scrollbar-arrow-color: #fff; padding:0px; text-align:center;'>
                    <table>
                        <tr>
                            {foreach item=defense from=$variables['sector']['defences']}
                            <td style='vertical-align:top; background: url(templates/{$variables['template']}/images/bg_alpha.png) repeat;'>
                                <div style='width:160px; font-size:12px;'>
                                    <div>
                                        <a href='modify_defences.php?defence_id={$defense['id']}'>
                                            <img src="templates/{$variables['template']}/images/{$defense['image']['src']}" style='border:0px; width:80px; height:60px' alt='{$defense['image']['alt']}'>
                                        </a>
                                        <div style='font-size:1em; color:#fff;'>{$defense['owner_name']}<br>( {$defense['quantity']} {$defense['name']} )</div>
                                    </div>
                                </div>
                            </td>
                            {/foreach}
                        </tr>
                    </table>
                </div>
                {/if}
            </td>

            <td style='width:200px; vertical-align:top;'>
                <!-- Cargo: Caption -->
                <table style='width:140px; border:0; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>
                    <tr style='vertical-align:top'>
                        <td style='padding:0px; width:8px; text-align:right;'><img style='width:8px; height:18px; border:0px; float:right;' src='templates/{$variables['template']}/images/lcorner.png' alt=''></td>
                        <td style='padding:0px; white-space:nowrap; background-color:#400040; text-align:center; vertical-align:middle;'><span style='font-size:0.75em; color:#fff;'><strong>{$langvars['l_cargo']}</strong></span></td>
                        <td style='padding:0px; width:8px; text-align:left;'><img style='width:8px; height:18px; border:0px; float:right;' src='templates/{$variables['template']}/images/rcorner.png' alt=''></td>
                    </tr>
                </table>

                <!-- Cargo -->
                <table style='width:150px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>
                    <tr>
                        <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050; padding:0px;'>
                            <table style='width:100%; border:0px; background-color:#500050; padding:1px; border-spacing:0px; margin-left:auto; margin-right:auto;'>
                                <tr>
                                    <td style='vertical-align:middle; white-space:nowrap; text-align:left;' >&nbsp;<img style='height:12px; width:12px;' alt="{$langvars['l_ore']}" src="templates/{$variables['template']}/images/ore.png">&nbsp;{$langvars['l_ore']}&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style='vertical-align:middle; white-space:nowrap; text-align:right;'><span class=mnu>&nbsp;{$variables['player']['ship']['cargo']['ore']}&nbsp;</span></td>
                                </tr>
                                <tr>
                                    <td style='white-space:nowrap; text-align:left'>&nbsp;<img style='height:12px; width:12px;' alt="{$langvars['l_organics']}" src="templates/{$variables['template']}/images/organics.png">&nbsp;{$langvars['l_organics']}&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style='white-space:nowrap; text-align:right'><span class=mnu>&nbsp;{$variables['player']['ship']['cargo']['organics']}&nbsp;</span></td>
                                </tr>
                                <tr>
                                    <td style='white-space:nowrap; text-align:left'>&nbsp;<img style='height:12px; width:12px;' alt="{$langvars['l_goods']}" src="templates/{$variables['template']}/images/goods.png">&nbsp;{$langvars['l_goods']}&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style='white-space:nowrap; text-align:right'><span class=mnu>&nbsp;{$variables['player']['ship']['cargo']['goods']}&nbsp;</span></td>
                                </tr>
                                <tr>
                                    <td style='white-space:nowrap; text-align:left'>&nbsp;<img style='height:12px; width:12px;' alt="{$langvars['l_energy']}" src="templates/{$variables['template']}/images/energy.png">&nbsp;{$langvars['l_energy']}&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style='white-space:nowrap; text-align:right;'><span class=mnu>&nbsp;{$variables['player']['ship']['cargo']['energy']}&nbsp;</span></td>
                                </tr>
                                <tr>
                                    <td style='white-space:nowrap; text-align:left;'>&nbsp;<img style='height:12px; width:12px;' alt="{$langvars['l_colonists']}" src="templates/{$variables['template']}/images/colonists.png">&nbsp;{$langvars['l_colonists']}&nbsp;</td>
                                </tr>
                                <tr>
                                    <td style='white-space:nowrap; text-align:right;'><span class=mnu>&nbsp;{$variables['player']['ship']['cargo']['colonists']}&nbsp;</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <br>

                <!-- Real Space: Caption -->
                <table style='width:140px; border:0; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>
                    <tr style='vertical-align:top'>
                        <td style='padding:0px; width:8px; text-align:right;'><img style='width:8px; height:18px; border:0px; float:right;' src='templates/{$variables['template']}/images/lcorner.png' alt=''></td>
                        <td style='padding:0px; white-space:nowrap; background-color:#400040; text-align:center; vertical-align:middle;'><span style='font-size:0.75em; color:#fff;'><strong>{$langvars['l_realspace']}</strong></span></td>
                        <td style='padding:0px; width:8px; text-align:left;'><img style='width:8px; height:18px; border:0px; float:right;' src='templates/{$variables['template']}/images/rcorner.png' alt=''></td>
                    </tr>
                </table>

                <!-- Real Space -->
                <table style='width:150px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>
                    <tr>
                        <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050; padding:0px;'>
                            <table style="width:100%;">
                                {foreach item=preset from=$variables['presets']}
                                    <tr>
                                        <td style="text-align:left;"><a class=mnu href="rsmove.php?engage=1&destination={$preset['sector']}">=&gt;&nbsp;{$preset['sector']}</a></td>
                                        <td style="text-align:right;">[<a class=mnu href=preset.php>{$langvars['l_set']}</a>]</td>
                                    </tr>
                                {/foreach}
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style='white-space:nowrap; height:2px; background-color:transparent;'></td>
                    </tr>
                    <tr>
                        <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050;'>
                            &nbsp;<a class=mnu href="rsmove.php">=&gt;&nbsp;{$langvars['l_main_other']}</a>&nbsp;<br>
                        </td>
                    </tr>
                </table>

                <br>

                <!-- Warp To: Caption -->
                <table style='width:140px; border:0; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>
                    <tr style='vertical-align:top'>
                        <td style='padding:0px; width:8px; text-align:right;'><img style='width:8px; height:18px; border:0px; float:right;' src='templates/{$variables['template']}/images/lcorner.png' alt=''></td>
                        <td style='padding:0px; white-space:nowrap; background-color:#400040; text-align:center; vertical-align:middle;'><span style='font-size:0.75em; color:#fff;'><strong>{$langvars['l_main_warpto']}</strong></span></td>
                        <td style='padding:0px; width:8px; text-align:left;'><img style='width:8px; height:18px; border:0px; float:right;' src='templates/{$variables['template']}/images/rcorner.png' alt=''></td>
                    </tr>
                </table>

                <!-- Warp To -->
                <table style='width:150px; border:0px; padding:0px; border-spacing:0px; margin-left:auto; margin-right:auto;'>
                    <tr>
                        <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050; text-align:center; padding:0px;'>
                            <div class=mnu>
                                <table style="width:100%">
                                {foreach item=sector_id from=$variables['sector']['links']}
                                    <tr>
                                        <td style='text-align:left;'><a class='mnu' href='move.php?sector={$sector_id}'>=&gt;&nbsp;{$sector_id}</a></td>
                                        <td style='text-align:right;'>[<a class='mnu' href='lrscan.php?sector={$sector_id}'>{$langvars['l_scan']}</a>]</td>
                                    </tr>
                                {/foreach}
                                </table>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style='white-space:nowrap; height:2px; background-color:transparent;'></td>
                    </tr>
                    <tr>
                        <td style='white-space:nowrap; border:#fff 1px solid; background-color:#500050; text-align:center;'>
                            <div class=mnu>
                                &nbsp;<a class=dis href="lrscan.php?sector=*">{$langvars['l_fullscan']}</a>&nbsp;<br>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
{/block}