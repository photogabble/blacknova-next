{*
    Blacknova Traders - A web-based massively multiplayer space combat and trading game
    Copyright (C) 2001-2014 Ron Harwood and the BNT development team.
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

    File: news.tpl
*}
{extends file="layout.tpl"}
{block name=body}
    <table width="73%" border="0" cellspacing="2" cellpadding="2">
        <tr>
            <td height="73" width="27%"><img src="{$template_dir}/images/bnnhead.png" width="312"
                                             height="123" alt="The Blacknova Network"></td>
            <td height="73" width="73%" bgcolor="#000" valign="bottom" align="right">
                <p><font size="-1">{$langvars['l_news_info_1']}<br>{$langvars['l_news_info_2']}
                        <br>{$langvars['l_news_info_3']}<br>{$langvars['l_news_info_4']}<br>{$langvars['l_news_info_5']}<br></font>
                </p>
                <p>{$langvars['l_news_for']} {$variables['day']}</p>
            </td>
        </tr>
        <tr>
            <td height="22" width="27%" bgcolor="#00001A">&nbsp;</td>
            <td height="22" width="73%" bgcolor="#00001A" align="right"><a
                        href="/news?startdate={$variables['previous_day']}">{$langvars['l_news_prev']}</a> - <a
                        href="/news?startdate={$variables['next_day']}">{$langvars['l_news_next']}</a></td>
        </tr>
        {foreach from=$variables['days_news'] item=item}
            <tr>
                <td bgcolor="#003" align="center" style="vertical-align:text-top;">{$item['headline']}</td>
                <td bgcolor="#003" style="vertical-align:text-top;"><p align="justify">{$item['newstext']}</p><br></td>
            </tr>
            {foreachelse}
            <tr>
                <td bgcolor="#00001A" align="center">{$langvars['l_news_flash']}</td>
                <td bgcolor="#00001A" align="right">{$langvars['l_news_none']}</td>
            </tr>
        {/foreach}
    </table>
{/block}