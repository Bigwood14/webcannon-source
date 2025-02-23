/******************************************
* Slider Bar Form Element Script
* 
* Original program copyright David Harrison
*   d_s_h2@hotmail.com
* Version 2 copyright Eric C. Davis
*   eric@10mar2001.com
* 
* Visit http://www.dynamicdrive.com for
*   loads of other scripts.
* 
* This notice MUST stay intact for use.
*
* Modified by Tom Westcott
* http://www.cyberdummy.co.uk
******************************************/
/*var x, lgap,
	pel = null;

function newValue(value, tag) {
	el = document.getElementById(tag);
	var i = (Number(value));
	el2 = document.getElementById(tag + "out");
	width = (el2.style.width.replace(/\D/g,"") - 11);
	if(i > width) i = width;
	if(i < 0) i = 0;
	el.style.marginLeft = i + "px";
	el.inputElement.value = i;
}

function mousetracker (event) {
	if (!event) {
		event = window.event;
	}
	x = event.clientX;
	if (pel != null) {
		var width = pel.sliderWidth;
		pel.style.marginLeft = ( ( (lgap + x) > width) ? width : ( ( (lgap + x) < 0) ? 0 : lgap + x ) ) + "px"; //>
		pel.inputElement.value = pel.style.marginLeft.replace(/\D/g,"");
		pel.inputElement.onDrag();
	}
}

function track () {
	pel = this;
	// added this so it mouse strays from the drag tab mouse up still releases it
	if (document.addEventListener) {
                document.addEventListener('mouseup', stop, false);
        } else {
		document.onmouseup = stop;		
        }
	pel.inputElement.onDragStart();
	lgap = parseInt(pel.style.marginLeft.replace(/\D/g,""));
	if (isNaN(lgap)) {
		lgap = 0;
	}
	lgap -= x;
}

function stop() {
	pel.inputElement.onDragStop();
	pel = null;

	if (document.removeEventListener) {
                document.removeEventListener('mouseup', stop, false);
        } else {
                document.onmouseup = null;
        }
}

function form_slider (el, width) {
	var wid 		= parseInt(width);
	var newEl 		= document.createElement("input");
	newEl.type 		= "hidden";
	var outDiv 		= document.createElement("div");
	outDiv.className	= "move";
	outDiv.style.width 	= (wid+11) + "px";
	outDiv.id		= el.id + "out";
	var inDiv 		= document.createElement("div");
	inDiv.className 	= "move2";
	inDiv.id 		= el.id + "in";
	var slider 		= document.createElement("div");
	slider.className 	= el.className;
	slider.id 		= el.id;
	// convert original form element to new form element
	for (key in el) {
		try {
			newEl[key] = el[key];
		} catch (er) {
			// whoops! Can't assign that property.
		}
	}
	newEl.type 		= "hidden";
	newEl.className 	= "";
	newEl.name 		= el.name;
	newEl.id 		= el.id + "hidden";
	// assign persistent properties to slider div
	slider.inputElement	= newEl;
	slider.sliderWidth 	= wid;
	// assign events
	if (slider.addEventListener) {
		slider.addEventListener('mousedown', track, false);
		slider.addEventListener('mouseup', stop, false);
	} else {
		slider.onmousedown = track;
		slider.onmouseup = stop;
	}
	// put the new elements in the document
	outDiv.appendChild(inDiv);
	outDiv.appendChild(slider);
	value = el.value;
	id = el.id;
	el.parentNode.insertBefore(outDiv, el);
	// remove the old element before inserting the new element
	el.parentNode.removeChild(el);
	outDiv.parentNode.insertBefore(newEl, outDiv);
	if(value > 0) {
		newValue(value, id);
	}
}

if (window.addEventListener) {
	document.addEventListener('mousemove', mousetracker, false);
} else if (window.attachEvent) {
	document.attachEvent('onmousemove', mousetracker);
} else {
	document.onmousemove = mousetracker;
}

$(document).ready(function()
{
		var el = document.getElementById("speed");
		el.onDragStart = function() {
			in_elem = document.getElementById('speedin').className = "move2ondrag";
		}
		el.onDrag = function() {
			var value_elem = document.getElementById('speed_value');
			value_elem.innerHTML = Math.round(Number(this.value/10)) + 's';	
		};
		el.onDragStop = function() {
			in_elem = document.getElementById('speedin').className = "move2";
		}
		form_slider(el, '600');
});*/



