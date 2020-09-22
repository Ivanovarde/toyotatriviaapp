<?php
class MasterPageLumiaBTS extends MasterPage{

	public static $rsMenuIzquierdo = "";

	function getHTML() {
		// LLamo al metodo de la clase padre
		parent::getHTML();

		// Me fijo si hay zonas para reemplazar
		$this->html = ReplaceZone::reemplazar($this->html);

		return $this->html;
	}

}
?>