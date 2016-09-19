var parentClass = ".productnamedesc";
var productList = document.querySelectorAll(parentClass);
var expandHeight = (document.querySelector(parentClass).offsetHeight + 55) + "px";
var beforeNode = ".loginsmall";

buildShareShopElements(productList, beforeNode, expandHeight);