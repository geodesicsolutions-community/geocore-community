<?php
//StringData.class.php
/**
 * Holds the geoStringData class.
 * 
 * @package System
 * @since Version 4.0.0
 */
/**************************************************************************
Geodesic Classifieds & Auctions Platform 18.02
Copyright (c) 2001-2018 Geodesic Solutions, LLC
All rights reserved
http://geodesicsolutions.com
see license attached to distribution
**************************************************************************/
##########GIT Build Data##########
## 
## File Changed In GIT Commit:
## ##    7.1.0-33-gc09304b
## 
##################################

/**
 * This class is to store accented chars, to be used by {@link geoString::removeAccents()}, mainly to keep the String.class.php file
 * un-cluttered.
 * 
 * @package System
 * @since Version 4.0.0
 */
class geoStringData
{
	/**
	 * Set this to 1, then have something call geoString::removeAccents() to
	 * display a tool that will help with adding new chars to the accents you
	 * want to convert.
	 * 
	 * @var bool
	 */
	private static $_newCharTool = false;
	/**
	 * Internal
	 * @internal
	 */
	private static $_accentMap;
	
	/**
	 * Initialize accents
	 * @internal
	 */
	private static function initAccents ()
	{
		if (isset(self::$_accentMap)) {
			//already done
			return;
		}
		//Strings to use for from/to in conversion. Once they
		//are decoded for use in this function, save them
		//in a static var so we don't have to keep decoding the
		//same string over and over.
		
		self::$_accentMap = unserialize(base64_decode(
'YTo1ODU6e3M6MToiSSI7czoxOiJJIjtzOjE6ImkiO3M6MToiaSI7czoyOiLCpSI7czoxOiJZIjtz'.
'OjI6IsK1IjtzOjE6InUiO3M6Mjoiw4AiO3M6MToiQSI7czoyOiLDgSI7czoxOiJBIjtzOjI6IsOC'.
'IjtzOjE6IkEiO3M6Mjoiw4MiO3M6MToiQSI7czoyOiLDhCI7czoxOiJBIjtzOjI6IsOFIjtzOjE6'.
'IkEiO3M6Mjoiw4YiO3M6MToiQSI7czoyOiLDhyI7czoxOiJDIjtzOjI6IsOIIjtzOjE6IkUiO3M6'.
'Mjoiw4kiO3M6MToiRSI7czoyOiLDiiI7czoxOiJFIjtzOjI6IsOLIjtzOjE6IkUiO3M6Mjoiw4wi'.
'O3M6MToiSSI7czoyOiLDjSI7czoxOiJJIjtzOjI6IsOOIjtzOjE6IkkiO3M6Mjoiw48iO3M6MToi'.
'SSI7czoyOiLDkCI7czoxOiJEIjtzOjI6IsORIjtzOjE6Ik4iO3M6Mjoiw5IiO3M6MToiTyI7czoy'.
'OiLDkyI7czoxOiJPIjtzOjI6IsOUIjtzOjE6Ik8iO3M6Mjoiw5UiO3M6MToiTyI7czoyOiLDliI7'.
'czoxOiJPIjtzOjI6IsOYIjtzOjE6Ik8iO3M6Mjoiw5kiO3M6MToiVSI7czoyOiLDmiI7czoxOiJV'.
'IjtzOjI6IsObIjtzOjE6IlUiO3M6Mjoiw5wiO3M6MToiVSI7czoyOiLDnSI7czoxOiJZIjtzOjI6'.
'IsOfIjtzOjE6IkIiO3M6Mjoiw6AiO3M6MToiYSI7czoyOiLDoSI7czoxOiJhIjtzOjI6IsOiIjtz'.
'OjE6ImEiO3M6Mjoiw6MiO3M6MToiYSI7czoyOiLDpCI7czoxOiJhIjtzOjI6IsOlIjtzOjE6ImEi'.
'O3M6Mjoiw6YiO3M6MToiYSI7czoyOiLDpyI7czoxOiJjIjtzOjI6IsOoIjtzOjE6ImUiO3M6Mjoi'.
'w6kiO3M6MToiZSI7czoyOiLDqiI7czoxOiJlIjtzOjI6IsOrIjtzOjE6ImUiO3M6Mjoiw6wiO3M6'.
'MToiaSI7czoyOiLDrSI7czoxOiJpIjtzOjI6IsOuIjtzOjE6ImkiO3M6Mjoiw68iO3M6MToiaSI7'.
'czoyOiLDsCI7czoxOiJvIjtzOjI6IsOxIjtzOjE6Im4iO3M6Mjoiw7IiO3M6MToibyI7czoyOiLD'.
'syI7czoxOiJvIjtzOjI6IsO0IjtzOjE6Im8iO3M6Mjoiw7UiO3M6MToibyI7czoyOiLDtiI7czox'.
'OiJvIjtzOjI6IsO4IjtzOjE6Im8iO3M6Mjoiw7kiO3M6MToidSI7czoyOiLDuiI7czoxOiJ1Ijtz'.
'OjI6IsO7IjtzOjE6InUiO3M6Mjoiw7wiO3M6MToidSI7czoyOiLDvSI7czoxOiJ5IjtzOjI6IsO/'.
'IjtzOjE6InkiO3M6MjoixIAiO3M6MToiQSI7czoyOiLEgSI7czoxOiJhIjtzOjI6IsSCIjtzOjE6'.
'IkEiO3M6MjoixIMiO3M6MToiYSI7czoyOiLEhCI7czoxOiJBIjtzOjI6IsSFIjtzOjE6ImEiO3M6'.
'MjoixIYiO3M6MToiQyI7czoyOiLEhyI7czoxOiJjIjtzOjI6IsSIIjtzOjE6IkMiO3M6MjoixIki'.
'O3M6MToiYyI7czoyOiLEiiI7czoxOiJDIjtzOjI6IsSLIjtzOjE6ImMiO3M6MjoixIwiO3M6MToi'.
'QyI7czoyOiLEjSI7czoxOiJjIjtzOjI6IsSOIjtzOjE6IkQiO3M6MjoixI8iO3M6MToiZCI7czoy'.
'OiLEkCI7czoxOiJEIjtzOjI6IsSRIjtzOjE6ImQiO3M6MjoixJIiO3M6MToiRSI7czoyOiLEkyI7'.
'czoxOiJlIjtzOjI6IsSUIjtzOjE6IkUiO3M6MjoixJUiO3M6MToiZSI7czoyOiLEliI7czoxOiJF'.
'IjtzOjI6IsSXIjtzOjE6ImUiO3M6MjoixJgiO3M6MToiRSI7czoyOiLEmSI7czoxOiJlIjtzOjI6'.
'IsSaIjtzOjE6IkUiO3M6MjoixJsiO3M6MToiZSI7czoyOiLEnCI7czoxOiJHIjtzOjI6IsSdIjtz'.
'OjE6ImciO3M6MjoixJ4iO3M6MToiRyI7czoyOiLEnyI7czoxOiJnIjtzOjI6IsSgIjtzOjE6Ikci'.
'O3M6MjoixKEiO3M6MToiZyI7czoyOiLEoiI7czoxOiJHIjtzOjI6IsSjIjtzOjE6ImciO3M6Mjoi'.
'xKQiO3M6MToiSCI7czoyOiLEpSI7czoxOiJoIjtzOjI6IsSmIjtzOjE6IkgiO3M6MjoixKciO3M6'.
'MToiaCI7czoyOiLEqCI7czoxOiJJIjtzOjI6IsSpIjtzOjE6ImkiO3M6MjoixKoiO3M6MToiSSI7'.
'czoyOiLEqyI7czoxOiJpIjtzOjI6IsSsIjtzOjE6IkkiO3M6MjoixK0iO3M6MToiaSI7czoyOiLE'.
'riI7czoxOiJJIjtzOjI6IsSvIjtzOjE6ImkiO3M6MjoixLAiO3M6MToiSSI7czoyOiLEsSI7czox'.
'OiJpIjtzOjI6IsS0IjtzOjE6IkoiO3M6MjoixLUiO3M6MToiaiI7czoyOiLEtiI7czoxOiJLIjtz'.
'OjI6IsS3IjtzOjE6ImsiO3M6MjoixLkiO3M6MToiTCI7czoyOiLEuiI7czoxOiJsIjtzOjI6IsS7'.
'IjtzOjE6IkwiO3M6MjoixLwiO3M6MToibCI7czoyOiLEvSI7czoxOiJMIjtzOjI6IsS+IjtzOjE6'.
'ImwiO3M6MjoixL8iO3M6MToiTCI7czoyOiLFgCI7czoxOiJsIjtzOjI6IsWBIjtzOjE6IkwiO3M6'.
'MjoixYIiO3M6MToibCI7czoyOiLFgyI7czoxOiJOIjtzOjI6IsWEIjtzOjE6Im4iO3M6MjoixYUi'.
'O3M6MToiTiI7czoyOiLFhiI7czoxOiJuIjtzOjI6IsWHIjtzOjE6Ik4iO3M6MjoixYgiO3M6MToi'.
'biI7czoyOiLFjCI7czoxOiJPIjtzOjI6IsWNIjtzOjE6Im8iO3M6MjoixY4iO3M6MToiTyI7czoy'.
'OiLFjyI7czoxOiJvIjtzOjI6IsWQIjtzOjE6Ik8iO3M6MjoixZEiO3M6MToibyI7czoyOiLFkiI7'.
'czoxOiJPIjtzOjI6IsWTIjtzOjE6Im8iO3M6MjoixZQiO3M6MToiUiI7czoyOiLFlSI7czoxOiJy'.
'IjtzOjI6IsWWIjtzOjE6IlIiO3M6MjoixZciO3M6MToiciI7czoyOiLFmCI7czoxOiJSIjtzOjI6'.
'IsWZIjtzOjE6InIiO3M6MjoixZoiO3M6MToiUyI7czoyOiLFmyI7czoxOiJzIjtzOjI6IsWcIjtz'.
'OjE6IlMiO3M6MjoixZ0iO3M6MToicyI7czoyOiLFniI7czoxOiJTIjtzOjI6IsWfIjtzOjE6InMi'.
'O3M6MjoixaAiO3M6MToiUyI7czoyOiLFoSI7czoxOiJzIjtzOjI6IsWiIjtzOjE6IlQiO3M6Mjoi'.
'xaMiO3M6MToidCI7czoyOiLFpCI7czoxOiJUIjtzOjI6IsWlIjtzOjE6InQiO3M6MjoixaYiO3M6'.
'MToiVCI7czoyOiLFpyI7czoxOiJ0IjtzOjI6IsWoIjtzOjE6IlUiO3M6MjoixakiO3M6MToidSI7'.
'czoyOiLFqiI7czoxOiJVIjtzOjI6IsWrIjtzOjE6InUiO3M6MjoixawiO3M6MToiVSI7czoyOiLF'.
'rSI7czoxOiJ1IjtzOjI6IsWuIjtzOjE6IlUiO3M6Mjoixa8iO3M6MToidSI7czoyOiLFsCI7czox'.
'OiJVIjtzOjI6IsWxIjtzOjE6InUiO3M6MjoixbIiO3M6MToiVSI7czoyOiLFsyI7czoxOiJ1Ijtz'.
'OjI6IsW0IjtzOjE6IlciO3M6MjoixbUiO3M6MToidyI7czoyOiLFtiI7czoxOiJZIjtzOjI6IsW3'.
'IjtzOjE6InkiO3M6MjoixbgiO3M6MToiWSI7czoyOiLFuSI7czoxOiJaIjtzOjI6IsW6IjtzOjE6'.
'InoiO3M6MjoixbsiO3M6MToiWiI7czoyOiLFvCI7czoxOiJ6IjtzOjI6IsW9IjtzOjE6IloiO3M6'.
'Mjoixb4iO3M6MToieiI7czoyOiLGgCI7czoxOiJiIjtzOjI6IsaBIjtzOjE6IkIiO3M6MjoixoIi'.
'O3M6MToiYiI7czoyOiLGgyI7czoxOiJiIjtzOjI6IsaHIjtzOjE6IkMiO3M6MjoixogiO3M6MToi'.
'YyI7czoyOiLGkSI7czoxOiJGIjtzOjI6IsaSIjtzOjE6ImYiO3M6MjoixpMiO3M6MToiRyI7czoy'.
'OiLGlyI7czoxOiJJIjtzOjI6IsaYIjtzOjE6IksiO3M6MjoixpkiO3M6MToiayI7czoyOiLGmiI7'.
'czoxOiJsIjtzOjI6IsafIjtzOjE6Ik8iO3M6MjoixqAiO3M6MToiTyI7czoyOiLGoSI7czoxOiJv'.
'IjtzOjI6IsakIjtzOjE6IlAiO3M6MjoixqUiO3M6MToicCI7czoyOiLGqyI7czoxOiJ0IjtzOjI6'.
'IsasIjtzOjE6IlQiO3M6Mjoixq8iO3M6MToiVSI7czoyOiLGsCI7czoxOiJ1IjtzOjI6IsayIjtz'.
'OjE6IlUiO3M6MjoixrMiO3M6MToiWSI7czoyOiLGtCI7czoxOiJ5IjtzOjI6Isa1IjtzOjE6Iloi'.
'O3M6MjoixrYiO3M6MToieiI7czoyOiLHjSI7czoxOiJBIjtzOjI6IseOIjtzOjE6ImEiO3M6Mjoi'.
'x48iO3M6MToiSSI7czoyOiLHkCI7czoxOiJpIjtzOjI6IseRIjtzOjE6Ik8iO3M6Mjoix5IiO3M6'.
'MToibyI7czoyOiLHkyI7czoxOiJVIjtzOjI6IseUIjtzOjE6InUiO3M6Mjoix5UiO3M6MToiVSI7'.
'czoyOiLHliI7czoxOiJ1IjtzOjI6IseXIjtzOjE6IlUiO3M6Mjoix5giO3M6MToidSI7czoyOiLH'.
'mSI7czoxOiJVIjtzOjI6IseaIjtzOjE6InUiO3M6Mjoix5siO3M6MToiVSI7czoyOiLHnCI7czox'.
'OiJ1IjtzOjI6IseeIjtzOjE6IkEiO3M6Mjoix58iO3M6MToiYSI7czoyOiLHoCI7czoxOiJBIjtz'.
'OjI6IsehIjtzOjE6ImEiO3M6Mjoix6IiO3M6MToiQSI7czoyOiLHoyI7czoxOiJhIjtzOjI6Isek'.
'IjtzOjE6IkciO3M6Mjoix6UiO3M6MToiZyI7czoyOiLHpiI7czoxOiJHIjtzOjI6IsenIjtzOjE6'.
'ImciO3M6Mjoix6giO3M6MToiSyI7czoyOiLHqSI7czoxOiJrIjtzOjI6IseqIjtzOjE6IlEiO3M6'.
'Mjoix6siO3M6MToicSI7czoyOiLHrCI7czoxOiJRIjtzOjI6IsetIjtzOjE6InEiO3M6Mjoix7Ai'.
'O3M6MToiaiI7czoyOiLHtCI7czoxOiJHIjtzOjI6Ise1IjtzOjE6ImciO3M6Mjoix7giO3M6MToi'.
'TiI7czoyOiLHuSI7czoxOiJuIjtzOjI6Ise6IjtzOjE6IkEiO3M6Mjoix7siO3M6MToiYSI7czoy'.
'OiLHvCI7czoxOiJBIjtzOjI6Ise9IjtzOjE6ImEiO3M6Mjoix74iO3M6MToiTyI7czoyOiLHvyI7'.
'czoxOiJvIjtzOjI6IsiAIjtzOjE6IkEiO3M6MjoiyIEiO3M6MToiYSI7czoyOiLIgiI7czoxOiJB'.
'IjtzOjI6IsiDIjtzOjE6ImEiO3M6MjoiyIQiO3M6MToiRSI7czoyOiLIhSI7czoxOiJlIjtzOjI6'.
'IsiGIjtzOjE6IkUiO3M6MjoiyIciO3M6MToiZSI7czoyOiLIiCI7czoxOiJJIjtzOjI6IsiJIjtz'.
'OjE6ImkiO3M6MjoiyIoiO3M6MToiSSI7czoyOiLIiyI7czoxOiJpIjtzOjI6IsiMIjtzOjE6Ik8i'.
'O3M6MjoiyI0iO3M6MToibyI7czoyOiLIjiI7czoxOiJPIjtzOjI6IsiPIjtzOjE6Im8iO3M6Mjoi'.
'yJAiO3M6MToiUiI7czoyOiLIkSI7czoxOiJyIjtzOjI6IsiSIjtzOjE6IlIiO3M6MjoiyJMiO3M6'.
'MToiciI7czoyOiLIlCI7czoxOiJVIjtzOjI6IsiVIjtzOjE6InUiO3M6MjoiyJYiO3M6MToiVSI7'.
'czoyOiLIlyI7czoxOiJ1IjtzOjI6IsiYIjtzOjE6IlMiO3M6MjoiyJkiO3M6MToicyI7czoyOiLI'.
'miI7czoxOiJUIjtzOjI6IsibIjtzOjE6InQiO3M6MjoiyJ4iO3M6MToiSCI7czoyOiLInyI7czox'.
'OiJoIjtzOjI6IsikIjtzOjE6IloiO3M6MjoiyKUiO3M6MToieiI7czoyOiLIpiI7czoxOiJBIjtz'.
'OjI6IsinIjtzOjE6ImEiO3M6MjoiyKgiO3M6MToiRSI7czoyOiLIqSI7czoxOiJlIjtzOjI6Isiq'.
'IjtzOjE6Ik8iO3M6MjoiyKsiO3M6MToibyI7czoyOiLIrCI7czoxOiJPIjtzOjI6IsitIjtzOjE6'.
'Im8iO3M6MjoiyK4iO3M6MToiTyI7czoyOiLIryI7czoxOiJvIjtzOjI6IsiwIjtzOjE6Ik8iO3M6'.
'MjoiyLEiO3M6MToibyI7czoyOiLIsiI7czoxOiJZIjtzOjI6IsizIjtzOjE6InkiO3M6MjoiyLci'.
'O3M6MToiaiI7czoyOiLIuiI7czoxOiJBIjtzOjI6Isi7IjtzOjE6IkMiO3M6MjoiyLwiO3M6MToi'.
'YyI7czoyOiLIvSI7czoxOiJMIjtzOjI6Isi+IjtzOjE6IlQiO3M6MjoiyL8iO3M6MToicyI7czoy'.
'OiLJgCI7czoxOiJ6IjtzOjI6IsmDIjtzOjE6IkIiO3M6MjoiyYYiO3M6MToiRSI7czoyOiLJhyI7'.
'czoxOiJlIjtzOjI6IsmKIjtzOjE6IlEiO3M6MjoiyYsiO3M6MToicSI7czoyOiLJjCI7czoxOiJS'.
'IjtzOjI6IsmNIjtzOjE6InIiO3M6MjoiyY4iO3M6MToiWSI7czoyOiLJjyI7czoxOiJ5IjtzOjI6'.
'IsmTIjtzOjE6ImIiO3M6MjoiyZUiO3M6MToiYyI7czoyOiLJoCI7czoxOiJnIjtzOjI6IsmoIjtz'.
'OjE6ImkiO3M6MjoiybUiO3M6MToibyI7czoyOiLJvCI7czoxOiJyIjtzOjI6IsqCIjtzOjE6InMi'.
'O3M6MjoiyosiO3M6MToidiI7czoyOiLKjyI7czoxOiJZIjtzOjI6IsqQIjtzOjE6IloiO3M6Mjoi'.
'yqAiO3M6MToiZCI7czozOiLhuIAiO3M6MToiQSI7czozOiLhuIEiO3M6MToiYSI7czozOiLhuIIi'.
'O3M6MToiQiI7czozOiLhuIMiO3M6MToiYiI7czozOiLhuIQiO3M6MToiQiI7czozOiLhuIUiO3M6'.
'MToiYiI7czozOiLhuIYiO3M6MToiQiI7czozOiLhuIciO3M6MToiYiI7czozOiLhuIgiO3M6MToi'.
'QyI7czozOiLhuIkiO3M6MToiYyI7czozOiLhuIoiO3M6MToiRCI7czozOiLhuIsiO3M6MToiZCI7'.
'czozOiLhuIwiO3M6MToiRCI7czozOiLhuI0iO3M6MToiZCI7czozOiLhuI4iO3M6MToiRCI7czoz'.
'OiLhuI8iO3M6MToiZCI7czozOiLhuJAiO3M6MToiRCI7czozOiLhuJEiO3M6MToiZCI7czozOiLh'.
'uJIiO3M6MToiRCI7czozOiLhuJMiO3M6MToiZCI7czozOiLhuJQiO3M6MToiRSI7czozOiLhuJUi'.
'O3M6MToiZSI7czozOiLhuJYiO3M6MToiRSI7czozOiLhuJciO3M6MToiZSI7czozOiLhuJgiO3M6'.
'MToiRSI7czozOiLhuJkiO3M6MToiZSI7czozOiLhuJoiO3M6MToiRSI7czozOiLhuJsiO3M6MToi'.
'ZSI7czozOiLhuJwiO3M6MToiRSI7czozOiLhuJ0iO3M6MToiZSI7czozOiLhuJ4iO3M6MToiRiI7'.
'czozOiLhuJ8iO3M6MToiZiI7czozOiLhuKAiO3M6MToiRyI7czozOiLhuKEiO3M6MToiZyI7czoz'.
'OiLhuKIiO3M6MToiSCI7czozOiLhuKMiO3M6MToiaCI7czozOiLhuKQiO3M6MToiSCI7czozOiLh'.
'uKUiO3M6MToiaCI7czozOiLhuKYiO3M6MToiSCI7czozOiLhuKciO3M6MToiaCI7czozOiLhuKgi'.
'O3M6MToiSCI7czozOiLhuKkiO3M6MToiaCI7czozOiLhuKoiO3M6MToiSCI7czozOiLhuKsiO3M6'.
'MToiaCI7czozOiLhuKwiO3M6MToiSSI7czozOiLhuK0iO3M6MToiaSI7czozOiLhuK4iO3M6MToi'.
'SSI7czozOiLhuK8iO3M6MToiaSI7czozOiLhuLAiO3M6MToiSyI7czozOiLhuLEiO3M6MToiayI7'.
'czozOiLhuLIiO3M6MToiSyI7czozOiLhuLMiO3M6MToiayI7czozOiLhuLQiO3M6MToiSyI7czoz'.
'OiLhuLUiO3M6MToiayI7czozOiLhuLYiO3M6MToiTCI7czozOiLhuLciO3M6MToibCI7czozOiLh'.
'uLgiO3M6MToiTCI7czozOiLhuLkiO3M6MToibCI7czozOiLhuLoiO3M6MToiTCI7czozOiLhuLsi'.
'O3M6MToibCI7czozOiLhuLwiO3M6MToiTCI7czozOiLhuL0iO3M6MToibCI7czozOiLhuL4iO3M6'.
'MToiTSI7czozOiLhuL8iO3M6MToibSI7czozOiLhuYAiO3M6MToiTSI7czozOiLhuYEiO3M6MToi'.
'bSI7czozOiLhuYIiO3M6MToiTSI7czozOiLhuYMiO3M6MToibSI7czozOiLhuYQiO3M6MToiTiI7'.
'czozOiLhuYUiO3M6MToibiI7czozOiLhuYYiO3M6MToiTiI7czozOiLhuYciO3M6MToibiI7czoz'.
'OiLhuYgiO3M6MToiTiI7czozOiLhuYkiO3M6MToibiI7czozOiLhuYoiO3M6MToiTiI7czozOiLh'.
'uYsiO3M6MToibiI7czozOiLhuYwiO3M6MToiTyI7czozOiLhuY0iO3M6MToibyI7czozOiLhuY4i'.
'O3M6MToiTyI7czozOiLhuY8iO3M6MToibyI7czozOiLhuZAiO3M6MToiTyI7czozOiLhuZEiO3M6'.
'MToibyI7czozOiLhuZIiO3M6MToiTyI7czozOiLhuZMiO3M6MToibyI7czozOiLhuZQiO3M6MToi'.
'UCI7czozOiLhuZUiO3M6MToicCI7czozOiLhuZYiO3M6MToiUCI7czozOiLhuZciO3M6MToicCI7'.
'czozOiLhuZgiO3M6MToiUiI7czozOiLhuZkiO3M6MToiciI7czozOiLhuZoiO3M6MToiUiI7czoz'.
'OiLhuZsiO3M6MToiciI7czozOiLhuZwiO3M6MToiUiI7czozOiLhuZ0iO3M6MToiciI7czozOiLh'.
'uZ4iO3M6MToiUiI7czozOiLhuZ8iO3M6MToiciI7czozOiLhuaAiO3M6MToiUyI7czozOiLhuaEi'.
'O3M6MToicyI7czozOiLhuaIiO3M6MToiUyI7czozOiLhuaMiO3M6MToicyI7czozOiLhuaQiO3M6'.
'MToiUyI7czozOiLhuaUiO3M6MToicyI7czozOiLhuaYiO3M6MToiUyI7czozOiLhuaciO3M6MToi'.
'cyI7czozOiLhuagiO3M6MToiUyI7czozOiLhuakiO3M6MToicyI7czozOiLhuaoiO3M6MToiVCI7'.
'czozOiLhuasiO3M6MToidCI7czozOiLhuawiO3M6MToiVCI7czozOiLhua0iO3M6MToidCI7czoz'.
'OiLhua4iO3M6MToiVCI7czozOiLhua8iO3M6MToidCI7czozOiLhubAiO3M6MToiVCI7czozOiLh'.
'ubEiO3M6MToidCI7czozOiLhubIiO3M6MToiVSI7czozOiLhubMiO3M6MToidSI7czozOiLhubQi'.
'O3M6MToiVSI7czozOiLhubUiO3M6MToidSI7czozOiLhubYiO3M6MToiVSI7czozOiLhubciO3M6'.
'MToidSI7czozOiLhubgiO3M6MToiVSI7czozOiLhubkiO3M6MToidSI7czozOiLhuboiO3M6MToi'.
'VSI7czozOiLhubsiO3M6MToidSI7czozOiLhubwiO3M6MToiViI7czozOiLhub0iO3M6MToidiI7'.
'czozOiLhub4iO3M6MToiViI7czozOiLhub8iO3M6MToidiI7czozOiLhuoAiO3M6MToiVyI7czoz'.
'OiLhuoEiO3M6MToidyI7czozOiLhuoIiO3M6MToiVyI7czozOiLhuoMiO3M6MToidyI7czozOiLh'.
'uoQiO3M6MToiVyI7czozOiLhuoUiO3M6MToidyI7czozOiLhuoYiO3M6MToiVyI7czozOiLhuoci'.
'O3M6MToidyI7czozOiLhuogiO3M6MToiVyI7czozOiLhuokiO3M6MToidyI7czozOiLhuooiO3M6'.
'MToiWCI7czozOiLhuosiO3M6MToieCI7czozOiLhuowiO3M6MToiWCI7czozOiLhuo0iO3M6MToi'.
'eCI7czozOiLhuo4iO3M6MToiWSI7czozOiLhuo8iO3M6MToieSI7czozOiLhupAiO3M6MToiWiI7'.
'czozOiLhupEiO3M6MToieiI7czozOiLhupIiO3M6MToiWiI7czozOiLhupMiO3M6MToieiI7czoz'.
'OiLhupQiO3M6MToiWiI7czozOiLhupUiO3M6MToieiI7czozOiLhupYiO3M6MToiaCI7czozOiLh'.
'upciO3M6MToidCI7czozOiLhuqAiO3M6MToiQSI7czozOiLhuqEiO3M6MToiYSI7czozOiLhuqIi'.
'O3M6MToiQSI7czozOiLhuqMiO3M6MToiYSI7czozOiLhuqQiO3M6MToiQSI7czozOiLhuqUiO3M6'.
'MToiYSI7czozOiLhuqYiO3M6MToiQSI7czozOiLhuqciO3M6MToiYSI7czozOiLhuqgiO3M6MToi'.
'QSI7czozOiLhuqkiO3M6MToiYSI7czozOiLhuqoiO3M6MToiQSI7czozOiLhuqsiO3M6MToiYSI7'.
'czozOiLhuqwiO3M6MToiQSI7czozOiLhuq0iO3M6MToiYSI7czozOiLhuq4iO3M6MToiQSI7czoz'.
'OiLhuq8iO3M6MToiYSI7czozOiLhurAiO3M6MToiQSI7czozOiLhurEiO3M6MToiYSI7czozOiLh'.
'urIiO3M6MToiQSI7czozOiLhurMiO3M6MToiYSI7czozOiLhurQiO3M6MToiQSI7czozOiLhurUi'.
'O3M6MToiYSI7czozOiLhurYiO3M6MToiQSI7czozOiLhurciO3M6MToiYSI7czozOiLhurgiO3M6'.
'MToiRSI7czozOiLhurkiO3M6MToiZSI7czozOiLhuroiO3M6MToiRSI7czozOiLhursiO3M6MToi'.
'ZSI7czozOiLhurwiO3M6MToiRSI7czozOiLhur0iO3M6MToiZSI7czozOiLhur4iO3M6MToiRSI7'.
'czozOiLhur8iO3M6MToiZSI7czozOiLhu4AiO3M6MToiRSI7czozOiLhu4EiO3M6MToiZSI7czoz'.
'OiLhu4IiO3M6MToiRSI7czozOiLhu4MiO3M6MToiZSI7czozOiLhu4QiO3M6MToiRSI7czozOiLh'.
'u4UiO3M6MToiZSI7czozOiLhu4YiO3M6MToiRSI7czozOiLhu4ciO3M6MToiZSI7czozOiLhu4gi'.
'O3M6MToiSSI7czozOiLhu4kiO3M6MToiaSI7czozOiLhu4oiO3M6MToiSSI7czozOiLhu4siO3M6'.
'MToiaSI7czozOiLhu4wiO3M6MToiTyI7czozOiLhu40iO3M6MToibyI7czozOiLhu44iO3M6MToi'.
'TyI7czozOiLhu48iO3M6MToibyI7czozOiLhu5AiO3M6MToiTyI7czozOiLhu5EiO3M6MToibyI7'.
'czozOiLhu5IiO3M6MToiTyI7czozOiLhu5MiO3M6MToibyI7czozOiLhu5QiO3M6MToiTyI7czoz'.
'OiLhu5UiO3M6MToibyI7czozOiLhu5YiO3M6MToiTyI7czozOiLhu5ciO3M6MToibyI7czozOiLh'.
'u5giO3M6MToiTyI7czozOiLhu5kiO3M6MToibyI7czozOiLhu5oiO3M6MToiTyI7czozOiLhu5si'.
'O3M6MToibyI7czozOiLhu5wiO3M6MToiTyI7czozOiLhu50iO3M6MToibyI7czozOiLhu54iO3M6'.
'MToiTyI7czozOiLhu58iO3M6MToibyI7czozOiLhu6AiO3M6MToiTyI7czozOiLhu6EiO3M6MToi'.
'byI7czozOiLhu6IiO3M6MToiTyI7czozOiLhu6MiO3M6MToibyI7czozOiLhu6QiO3M6MToiVSI7'.
'czozOiLhu6UiO3M6MToidSI7czozOiLhu6YiO3M6MToiVSI7czozOiLhu6ciO3M6MToidSI7czoz'.
'OiLhu6giO3M6MToiVSI7czozOiLhu6kiO3M6MToidSI7czozOiLhu6oiO3M6MToiVSI7czozOiLh'.
'u6siO3M6MToidSI7czozOiLhu6wiO3M6MToiVSI7czozOiLhu60iO3M6MToidSI7czozOiLhu64i'.
'O3M6MToiVSI7czozOiLhu68iO3M6MToidSI7czozOiLhu7AiO3M6MToiVSI7czozOiLhu7EiO3M6'.
'MToidSI7czozOiLhu7IiO3M6MToiWSI7czozOiLhu7MiO3M6MToieSI7czozOiLhu7QiO3M6MToi'.
'WSI7czozOiLhu7UiO3M6MToieSI7czozOiLhu7YiO3M6MToiWSI7czozOiLhu7ciO3M6MToieSI7'.
'czozOiLhu7giO3M6MToiWSI7czozOiLhu7kiO3M6MToieSI7czozOiLisaAiO3M6MToiTCI7czoz'.
'OiLisaEiO3M6MToibCI7czozOiLisaIiO3M6MToiTCI7czozOiLisaMiO3M6MToiUCI7czozOiLi'.
'saQiO3M6MToiUiI7czozOiLisaUiO3M6MToiYSI7czozOiLisaYiO3M6MToidCI7czozOiLisaci'.
'O3M6MToiSCI7czozOiLisagiO3M6MToiaCI7czozOiLisakiO3M6MToiSyI7czozOiLisaoiO3M6'.
'MToiayI7czozOiLisasiO3M6MToiWiI7czozOiLisawiO3M6MToieiI7czozOiLisbQiO3M6MToi'.
'diI7fQ=='.
''));
		
		self::showAccentTools();
	}
	
	/**
	 * Gets the accent chars map.  Used internally mostly.
	 * @return array
	 */
	public static function getAccentMap ()
	{
		self::initAccents();
		return self::$_accentMap;
	}
	
	/**
	 * Gets the UTF8 accented chars, used internally.
	 * @return array
	 */
	public static function getAccentCharsUtf8 ()
	{
		self::initAccents();
		return self::$_accentCharsUtf8;
	}
	
	/**
	 * Handy tool to allow us developers to add additional accented chars if needed.
	 * 
	 * @param bool $force
	 */
	public static function showAccentTools ($force = false)
	{
		if (!self::$_newCharTool && !$force) {
			//only if set to true
			return;
		}
		//change to true to display current chars un-encoded, and to help with re-encoding to add new chars
		
		if (isset($_POST['rawChars'])) {
			$rawChars = trim($_POST['rawChars']);// preg_replace('/[\t\n\s]+/','',trim($_POST['rawChars']));
			
			//NOTE:  It only gets to this part if on local test installation, never
			//on live sites.
			if (self::$_newCharTool) {
				$rawChars = eval($rawChars);
				
				if (is_array($rawChars)) {
					//it is an array
					ksort($rawChars);
					
					$encoded = "self::\$_accentMap = unserialize(base64_decode(\n'";
					$encoded .= chunk_split(base64_encode(serialize($rawChars)), 76, "'.\n'");
					$encoded .= "'));";
				} else {
					$encoded = "INVALID INPUT";
				}
			} else {
				//allow it to show the array, but don't generate the new array
				//if something attempts to force the tool to run
				$encoded = "ONLY IF TOOL TURNED ON";
			}
		}
		
		$map = (isset($rawChars) && is_array($rawChars))? $rawChars : self::$_accentMap;
		
		$toVars = array_values($map);
		$fromVars = array_keys($map);
		
		$raw = "return array (\n";
		
		foreach ($fromVars as $i => $from) {
			$from = (geoString::isUTF8($from))? $from : utf8_encode($from);
			
			$raw .= "	'$from' => '{$toVars[$i]}',\n";
		}
		$raw .= ");";
		
		?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Charset Editing Tool</title>
	<style type="text/css">
		pre {
			vertical-align: middle;
			margin: 10px;
			padding: 10px;
			padding-left: 28px;
			background-image: none;
			background-position: 1px 1px;
			background-repeat: no-repeat;
			border: 1px solid rgb(128,128,128);
			background-color: rgb(243,243,243);	
		}
		textarea {
			width: 600px;
			height: 300px;
			vertical-align: middle;
			margin: 10px;
			padding: 10px;
			padding-left: 28px;
			background-image: none;
			background-position: 1px 1px;
			background-repeat: no-repeat;
			border: 1px solid rgb(128,128,128);
			background-color: rgb(243,243,243);	
		}
	</style>
</head>
<body>
	<h1>Developer Charset Editing Tools</h1>
	<p>
		Since accented chars have to be base64 encoded in order to
		prevent corruption of the file, this tool is here to help make it slightly easier to add new
		accented chars to be converted by the geoString::removeAccents() function.
		<br /><br />
		<strong>How this works:</strong>
	</p>
	<ol>
		<li>
			In <strong>classes/php5_classes/StringData.class.php</strong>, find the line
			<pre>private static $_newCharTool = false;</pre>
			<br />
			And change it to = <strong>true</strong>.  You've already done this if reading this
			on the page.
		</li>
		<li>
			In <strong>app_top.main.php</strong> add the line near the bottom:
			<pre>geoString::removeAccents('a');</pre>
			<br />This way, this tool will be the only thing on the page.
		</li>
		<li>
			In the textarea below, type in the additional chars you want to add
			to the array to convert, then click "submit".  If you're adding a bunch, might
			edit in text editor in case form doesn't submit.
		</li>
		<li>
			An updated base 64 encoded string will be displayed.  Copy/paste it
			into the file <strong>classes/php5_classes/StringData.class.php</strong>.
		</li>
		<li>Un-do what you did in step 1 and 2.</li>
		<li>Test the chars added!</li>
	</ol>
	<br /><br />
	<strong>Raw Array: (Edit this)</strong><br />
	<form action="" method="post">
		<textarea name="rawChars"><?php echo $raw; ?></textarea>
		<br />
		<input type="submit" value="Submit" />
	</form>
	<?php if ($encoded) { ?>
		<strong>Encoded Data:  (copy/paste this)</strong>
		<pre><?php echo $encoded; ?></pre>
	<?php } ?>
</body>
</html><?php 
		include GEO_BASE_DIR . 'app_bottom.php';
		exit;
	}
}