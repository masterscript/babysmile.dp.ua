tinyMCEPopup.requireLangPack();

function init() {
	tinyMCEPopup.resizeToInnerSize();

	var inst = tinyMCEPopup.editor;
	var dom = inst.dom;
	var trElm = dom.getParent(inst.selection.getNode(), "tr");
	var formObj = document.forms[0];
	var st = dom.parseStyle(dom.getAttrib(trElm, "style"));

	// Get table row data
	var rowtype = trElm.parentNode.nodeName.toLowerCase();
	var align = dom.getAttrib(trElm, 'align');
	var valign = dom.getAttrib(trElm, 'valign');
	var height = trimSize(getStyle(trElm, 'height', 'height'));
	var className = dom.getAttrib(trElm, 'class');

	// Setup form
	formObj.height.value = height;
	selectByValue(formObj, 'align', align);
	selectByValue(formObj, 'valign', valign);

}

function updateAction() {
	var inst = tinyMCEPopup.editor;
	var dom = inst.dom;
	var trElm = dom.getParent(inst.selection.getNode(), "tr");
	var tableElm = dom.getParent(inst.selection.getNode(), "table");
	var formObj = document.forms[0];
	inst.execCommand('mceBeginUndoLevel');

	updateRow(trElm);

	inst.addVisual();
	inst.nodeChanged();
	inst.execCommand('mceEndUndoLevel');
	tinyMCEPopup.close();
}

function updateRow(tr_elm, skip_id, skip_parent) {
	var inst = tinyMCEPopup.editor;
	var formObj = document.forms[0];
	var dom = inst.dom;
	var doc = inst.getDoc();

	tr_elm.setAttribute('align', getSelectValue(formObj, 'align'));
	tr_elm.setAttribute('vAlign', getSelectValue(formObj, 'valign'));
}

function changedStyle() {
	var formObj = document.forms[0], dom = tinyMCEPopup.editor.dom;
	var st = dom.parseStyle(formObj.style.value);

	if (st['height'])
		formObj.height.value = trimSize(st['height']);
}

function changedSize() {
	var formObj = document.forms[0], dom = tinyMCEPopup.editor.dom;
	var st = dom.parseStyle(formObj.style.value);

	var height = formObj.height.value;
	if (height != "")
		st['height'] = getCSSSize(height);
	else
		st['height'] = "";

}

tinyMCEPopup.onInit.add(init);
