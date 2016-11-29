"use strict";

// template search results onto the page
function displayResults(target, json){
    var template = $("#resultTemplate").html();
    var searchResults = Mustache.to_html(template, json.data);
    $("."+target ).html(searchResults);
}

// run a search against the gateway
function doSearch(target, needle) {
    var myurl="http://localhost:8888/search?t="+target+"&q="+needle+"&n=10";
    $.ajax({
	url: myurl,
	dataType: "jsonp",
	success: function (result){  return displayResults(target, result);},
    });
}

// load search spcified by query params
function loadSearch(){
    // may need to polyfill this
    var urlParams = new URLSearchParams(window.location.search);
    var needle = urlParams.get("q");
    $( "input:first" ).val(needle);
    if(needle) {
	mySearches.forEach( function(srch) { srch(needle); });
    }
}

// search form handler
function submitSearch( event ) {
    var needle=$( "input:first" ).val();
    history.pushState({}, "Search", window.location.pathname + "?q=" +needle );
    if(needle) {
	mySearches.forEach( function(srch) { srch(needle); });
    }
    return false;
}

// available searches 
var mySearches = [];
mySearches.push( function(needle) { return doSearch("primobook", needle); });
mySearches.push( function(needle) { return doSearch("libguides", needle); });

$( document ).ready(function() {
    // do search spcified in query params 
    loadSearch() ;
    // set up search form handler
    $( "form#searchForm" ).submit( submitSearch);
});
