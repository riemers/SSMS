function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      oldonload();
      func();
    }
  }
}

function prepareInputsForHints() {
        var inputs = document.getElementsByTagName("input");
        for (var i=0; i<inputs.length; i++){
                // test to see if the hint span exists first
                if (inputs[i].parentNode.getElementsByTagName("span")[0]) {
                        // the span exists!  on focus, show the hint
                        inputs[i].onfocus = function () {
                                this.parentNode.getElementsByTagName("span")[0].style.display = "inline";
                        }
                        // when the cursor moves away from the field, hide the hint
                        inputs[i].onblur = function () {
                                this.parentNode.getElementsByTagName("span")[0].style.display = "none";
                        }
                }
        }
        // repeat the same tests as above for selects
        var selects = document.getElementsByTagName("select");
        for (var k=0; k<selects.length; k++){
                if (selects[k].parentNode.getElementsByTagName("span")[0]) {
                        selects[k].onfocus = function () {
                                this.parentNode.getElementsByTagName("span")[0].style.display = "inline";
                        }
                        selects[k].onblur = function () {
                                this.parentNode.getElementsByTagName("span")[0].style.display = "none";
                        }
                }
        }
}
addLoadEvent(prepareInputsForHints);
