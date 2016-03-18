/* TaalPortaal.js */
/*
author: Frank Landsbergen (INL)

version 1.0 date: 25.06.2014
version 1.1 date: 30.06.2014 set default sentence in PHP script
version 1.2 date: 25.02.2015 deal with diacritics
version 1.3 date: 09.03.2016 added dummy examplefiller initialisation

included in GrETEL/Nederbooms by Liesbeth Augustinus
*/

jQuery(document).ready(function() {
    fillInputField(); //taalportaalextensie
});

function fillInputField() {

    var tpinput = getUrlVars()["tpinput"], //Zoek specifiek op waarde van 'tpinput'
        examplefiller = "Dit is een zin.";

    if (getUrlVars()["tpinput"]) {
        tpinput = tpinput.split('%20').join(' '); //zet %20 om in spaties
        tpinput = tpinput.split('+').join(' '); //(voor de zekerheid) zet + om in spaties
        tpinput = tpinput.split('%22').join('"'); //zet %22 om in "
        tpinput = tpinput.split('%27').join("'");
        tpinput = tpinput.split('%C3%A9').join("é");
        tpinput = tpinput.split('%C3%A8').join("è");
        tpinput = tpinput.split('$').join("=");

        examplefiller = tpinput;
    }
    //Verschillende opties voor de verschillende pagina's:
    //(1) gretel voor lassy, gretel voor cgn: inputveld heeft id=example:
    $('#example').val(examplefiller);
    //(2) stringsearch in lassy, stringsearch in cgn: inputveld heeft name=string;
    $('input[name=string]').val(examplefiller);
    //(3) alpino: inputveld heeft name=input
    $('input[name=input]').val(examplefiller);
    //(4) XPath search: inputveld heeft name=xpinput
    $('textarea[name=xpinput]').val(examplefiller);
}

function getUrlVars() {
    var vars = [],
        hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}
