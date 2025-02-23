function openWin(gotoURL,windowTitle,w,he,s)
{
    var newwin;
    newwin = window.open(gotoURL,windowTitle,'width='+w+', height='+he+', scrollbars='+s+'');
    newwin.focus();
}

function setBoxes()
{
    //alert(document.form.type.value);
    if(document.form.type.value == '1')
    {
        document.form.text.disabled = true;
        document.form.html.disabled = false;
    }
    else if(document.form.type.value == '0')
    {
        document.form.text.disabled = false;
        document.form.html.disabled = true;
    }
    else
    {
        document.form.text.disabled = false;
        document.form.html.disabled = false;
    }
}

function confirmSubmit(msg)
{
    var agree=confirm(msg);
    if (agree)
    return true ;
    else
    return false ;
}

function insertMM(putTo,from)
{
    var chaineAj;
    var selInd;
    var current;
    var myQuery;
    var left = "{";
    var right = "}";
    //eval("selInd = document.form."+from+".selectedIndex;");
    eval("myQuery = document.form."+putTo+"");
    eval("myListBox = document.form."+from+"");

    //eval("chaineAj = document.form."+from+".options["+selInd+"].value");

    if(myListBox.options.length > 0) {
        var chaineAj = "";
        var NbSelect = 0;
        for(var i=0; i<myListBox.options.length; i++)
        {
            if (myListBox.options[i].selected)
            {
                NbSelect++;
                if (NbSelect > 1)
                {
                    chaineAj += ", ";
                }
                chaineAj += myListBox.options[i].value;
                //alert(myListBox.options[i].value);
            }
        }
        // IE
        if (document.selection)
        {
            myQuery.focus();
            sel = document.selection.createRange();
            sel.text = left+chaineAj+right;
            //document.sqlform.insert.focus();
        }
        //MOZILLA/NETSCAPE support
        else if (myQuery.selectionStart || myQuery.selectionStart == "0")
        {
            var startPos = myQuery.selectionStart;
            var endPos = myQuery.selectionEnd;
            var chaineSql = myQuery.value;

            myQuery.value = chaineSql.substring(0, startPos) + left + chaineAj + right + chaineSql.substring(endPos, chaineSql.length);
        }
        else
        {
            myQuery.value += left+chaineAj+right;
        }

    }
}

function doTooltip(e, msg)
{
    if ( typeof Tooltip == "undefined" || !Tooltip.ready ) return;
    Tooltip.show(e, msg);
}

function hideTip()
{
    if ( typeof Tooltip == "undefined" || !Tooltip.ready ) return;
    Tooltip.hide();
}

function setHelp()
{
    var blah = getCookie("help_me");
    var sel = document.getElementById('help_me');
    
    if(blah == 'yes')
    {
        sel.checked = true;
    }
    
    helpMe();
}

function helpMe()
{
    var sel = document.getElementById('help_me');

    if(sel.checked == true)
    {
        var i =0;
        setCookie("help_me","yes");

        for(i = 0;i < 10;i ++)
        {
            var sell = document.getElementById('help_me_content_'+i);
            if(sell)
            {
                sell.style.display = 'block';
                
            }
        }
    }
    else
    {
        var i =0;
        setCookie("help_me","no");
        for(i = 0;i < 10;i ++)
        {
            var sell = document.getElementById('help_me_content_'+i);
            if(sell)
            {
                sell.style.display = 'none';
            }
        }
    }
}
