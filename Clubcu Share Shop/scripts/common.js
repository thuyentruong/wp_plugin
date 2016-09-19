// CONSTANCE
var classContainer = "shop-share-form";
var buttons = ["share-btn", "shop-btn", "admin-btn"];
var textButtons = ["Share", "Shop", "Admin"];
var opTextButtons = ["Remove", "Shop", "Admin"];

function $(s, elem) {
  elem = elem || document;
  return elem.querySelector(s);
}

function createTag(name, className, innerHTML) {
  var tag = document.createElement(name);
  tag.className = className;

  if (innerHTML) {
    tag.innerHTML = innerHTML;
  }

  return tag;
}

function createButton(className, textButton){
    var btn = document.createElement("BUTTON");
    btn.className = className;
    var t = document.createTextNode(textButton);
    btn.appendChild(t);
    return btn;
  }

function insertAfter(newNode, referenceNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}

function createShareShopElement(product, beforeNode, expandHeight){

    var element = product.querySelector(beforeNode);

    var container = createTag('div', classContainer);

    for( var i = 0; i < buttons.length; i++){

        if (i == 0) {
            
        }
       button = createButton(buttons[i], textButtons[i]);

        container.appendChild(button);
    }

  insertAfter(container, element);
  if (expandHeight != null) product.style.height = expandHeight;
}

function buildShareShopElements(productList, beforeNode, expandHeight){

    requestAPI("GET", "http://virtuo-develop.elasticbeanstalk.com/api/v1/common/email_is_existed?email=csr%40mailinator.com", null, build);
}

function build(xhr){
    var resp = JSON.parse(xhr.responseText);
    console.log(resp);
    console.log(resp.is_existed);

    for(var i = 0; i < productList.length; i++){
        createShareShopElement(productList[i], beforeNode, expandHeight);
        
    }
    attachEvent();
}

function attachEvent(){
    document.body.addEventListener("click", function (event) {
        // Event for SHARE button
        if (event.target.classList.contains(buttons[0])) {

            event.preventDefault();
            var productInfos = getProductInfo(event.target);
            console.log("Click SHARE button product_id: " + productInfos.productId + " category_id " + productInfos.categoryId);
            requestAPI("POST", "http://virtuo-develop.elasticbeanstalk.com/api/v1/sessions", "email=csr%40mailinator.com&password=12345678", sharePOST);
        }

        //  Event for SHOP button
        if (event.target.classList.contains(buttons[1])) {
            event.preventDefault();
            var productInfos = getProductInfo(event.target);
            console.log("Click SHOP button product_id: " + productInfos.productId + " category_id " + productInfos.categoryId);
            requestAPI("POST", "http://virtuo-develop.elasticbeanstalk.com/api/v1/sessions", "email=csr%40mailinator.com&password=12345678", sharePOST);
        }

        // Event for Admin button
        if (event.target.classList.contains(buttons[2])) {
            event.preventDefault();
            var productInfos = getProductInfo(event.target);
            console.log("Click ADMIN button product_id: " + productInfos.productId + " category_id " + productInfos.categoryId);
        }

    });
}

function requestAPI(method, requestURL, data, cfunc){
    var async = false;
    var xhr = new XMLHttpRequest();
    xhr.open(method, requestURL, async);
    if (method == 'POST'){
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    } else {
        xhr.setRequestHeader("Content-type", "application/json");
    }

    xhr.onreadystatechange = function() {
      if (xhr.readyState == 4) {
        // JSON.parse does not evaluate the attacker's scripts.
        cfunc(xhr);
      }
    }
    data == null ? xhr.send() : xhr.send(data);
}

function sharePOST(xhr, data){
    var resp = JSON.parse(xhr.responseText);
    console.log(resp);
    console.log(resp.user);
}

function shopPOST(xhr){
    var resp = JSON.parse(xhr.responseText);
    console.log(resp);
    console.log(resp.user);
}

function checkProduct(xhr){
    var resp = JSON.parse(xhr.responseText);
    console.log(resp);
    console.log(resp.user);
}


function getProductInfo(button){
    var link = button.closest(parentClass).querySelector("a").pathname;
    var infos = link.trim().split("/")
    return {
        categoryId: infos[2],
        productId: infos[4]
    };
}