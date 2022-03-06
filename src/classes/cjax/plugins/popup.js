
function popup(buffer)
{
    var params = buffer.array();
    //TODO: Undo this once it is decoded automatically
    var content =   CJAX.decode(params[0]);

    var title = 'New_Window';
    //alert(CJAX.dir_name(window.location));

    var newWindow = window.open("",title,"width=680,height=600,resizable,scrollbars=yes");
    newWindow.document.write(content);
    newWindow.document.close();
}
