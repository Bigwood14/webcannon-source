function openSubCategory(n)
{
    var sel = document.getElementById('insideSubCategory'+n);
    sel.style.display = 'block';
}

function closeSubCategory(n)
{
    var sel = document.getElementById('insideSubCategory'+n);
    sel.style.display = 'none';
}

function setCookie(name, value)
{
    var expire = new Date();
    var today = new Date();
    var path = "/";
    var domain = "";
    expire.setTime(today.getTime() + 3600000*24*30);
    
    document.cookie= name + "=" + escape(value) +
        ((expire) ? "; expires=" + expire.toGMTString() : "") +
        ((path) ? "; path=" + path : "") +
        ((domain != "") ? "; domain=" + domain : "");
       // ((secure) ? "; secure" : "");
}

function getCookie(name)
{
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1)
    {
        begin = dc.indexOf(prefix);
        if (begin != 0) return null;
    }
    else
    {
        begin += 2;
    }
    var end = document.cookie.indexOf(";", begin);
    if (end == -1)
    {
        end = dc.length;
    }
    return unescape(dc.substring(begin + prefix.length, end));
}

function setMenu()
{
    var item= new Array()
    item[0]="list_management";
    item[1]="scheduling";
    item[2]="reporting";
    item[3]="isp";
    item[4]="extra";
    item[5]="content";
    item[6]="server";
    item[7]="options";

    var x = 0;

    for (x=0; x<8; x++)
    {
        var blah = getCookie(item[x]);
        var theid = item[x];
        if(blah == 'open')
        {
            var sel = document.getElementById('insideMenu'+x);
            sel.style.display = 'block';
            document[theid].src = '/images/misc/minus.gif';
            setCookie(theid,"open");
        }
        else
        {
            var sel = document.getElementById('insideMenu'+x);
            sel.style.display = 'none';
            document[theid].src = '/images/misc/plus.gif';
            setCookie(theid,"close");  
        }
    }
}

function menuOpenClose(n, theid)
{
    var sel = document.getElementById('insideMenu'+n);

    if(sel.style.display == 'block')
    {
        sel.style.display = 'none';
        document[theid].src = '/images/misc/plus.gif';
        setCookie(theid,"close");
    }
    else
    {
        sel.style.display = 'block';
        document[theid].src = '/images/misc/minus.gif';
        setCookie(theid,"open");
    }
}
