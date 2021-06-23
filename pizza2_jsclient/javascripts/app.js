"use strict";
// avoid warnings on using fetch and Promise --
/* global fetch, Promise */
// use port 80, i.e., apache server for webservice execution 
// Change localhost to localhost:8000 to use the tunnel to pe07's port 80
const baseUrl = "http://localhost/cs637/smit999/pizza2_server/api";
// globals representing state of data and UI
let selectedUser = 'none';
let user_id;
let sizes = [];
let toppings = [];
let users = [];
let orders = [];
var orderToppings = [];
let lastOrderId;
let currentDay;
//let inprogressOrders = [];

let main = function () {//(sizes, toppings, users, orders) {
    //sizes = this.getSizes();
    setupTabs();  // for home/order pizza 
    // for home tab--
    displaySizesToppingsOnHomeTab();
    setupUserForm();
    setupRefreshOrderListForm();
    setupAcknowledgeForm();
    displayOrders();
    // for order tab--
    setupOrderForm();
    displaySizesToppingsOnOrderForm();
};

function displaySizesToppingsOnHomeTab() {
    let sizContents = document.querySelector("#sizes");
    sizes.forEach(function (size) {
        let sizeList = document.createElement("li");
        sizeList.className = "horizontal";
        sizeList.innerHTML = size['size'];
        sizContents.append(sizeList);       
    });
    let topContents = document.querySelector("#toppings");
    toppings.forEach(function (top) {
        let topList=document.createElement("li");
        topList.className = "horizontal";
        topList.innerHTML = top['topping'] ;
        topContents.append(topList);
    });
}

// Suggested step 3: implement this, and get_users
function setupUserForm() {
    let userselect = document.querySelector("#userselect");
    userselect.innerHTML +="<option value = 'none' selected> None </option>";
    users.forEach(function (user){
        let options = document.createElement("option");
        options.value = user['username'];
        options.innerHTML = user['username'];
        userselect.append(options);
    });
    document.querySelector("#userform input").addEventListener("click",event => {
        selectedUser = userselect.value;
        console.log("user.. sel user : " +selectedUser);
        displayOrders();
        event.preventDefault();
        
    });
    let orderInfo = document.querySelector("#order-area");
    let userFillin1 = document.querySelector("#username-fillin1");
    let userFillin2 = document.querySelector("#username-fillin2");
    userFillin1.id = selectedUser;
    userFillin2.id = selectedUser;
    orderInfo.classList.add("active");
    //displayOrders();
    //refreshData(main);
    //setupRefreshOrderListForm();
    // find the element with id userselect
    // create <option> elements with value = username, for
    // each user with the current user selected, 
    // plus one for user "none".
    // Add a click listener that finds out which user was
    // selected, make it the "selectedUser", and fill it in the
    //  "username-fillin" spots in the HTML.
    //  Also change the visibility of the order-area
    // and redisplay the orders
}
function findUserId(userName){
    var userId;
    users.forEach( function (usr){
            if(usr['username'] == userName){
                userId = usr['id'];
            }    
        });
    console.log("findUserId..." + userId );
    return userId;
}

// suggested step 7, and needs update_order
function setupAcknowledgeForm() {
    console.log("setupAckForm...");
    document.querySelector("#ackform input").addEventListener("click", event => {
        console.log("ack by user = " + selectedUser);
        // find this user's info in users, and their selectedUserId
        let selectedUserId = findUserId(selectedUser) ; // bogus value for now
        orders.forEach(function (order) {
            console.log("cking order = %O", order);
            if (order['user_id'] === selectedUserId && order.status === 'Baked') {
                console.log("Found baked order for user " + order.username);
                order.status = 'Finished';
                updateOrder(order, () => console.log("back from fetch for upd")); // post update to server
            }
        });
        displayOrders();
        event.preventDefault();
    });
    
}

// suggested steps: this should work once displayOrders works
function setupRefreshOrderListForm() {
    console.log("setupRefreshForm...");
    document.querySelector("#refreshbutton input").addEventListener("click", event => {
        console.log("refresh orders by user = " + selectedUser);
        getOrders(() => displayOrders());
        event.preventDefault();
    });
}
// suggested step 4, and needs get_orders
function displayOrders() {
    console.log("displayOrders " + selectedUser);
    let orderArea = document.querySelector("#order-area");
    let orderMessageId = document.querySelector("#ordermessage");
    let orderTableId = document.querySelector("#ordertable");
    orderArea.classList.remove("active"); 
    if(selectedUser === 'none'){
        orderMessageId.innerHTML = "nothing to do";
        //return;
    }else{
        orderMessageId.innerHTML = selectedUser + ":" + user_id;
    }  
    orderTableId.innerHTML = "";
    orderArea.classList.add("active");
   //let user_id;
   user_id = findUserId(selectedUser);
   orderMessageId.innerHTML = selectedUser;
   let inprogressOrders = [];
    orders.forEach(function (order){
        if (order.status === 'Preparing' && order.user_id === user_id ){
            inprogressOrders.push(order);
            console.log("inside progress func : " + inprogressOrders[0].status);
        }
    });
    orders.forEach(function (order){
        if ((order.status === 'Baked') && order.user_id === user_id ){
            inprogressOrders.push(order);
            console.log("inside baked func : " + inprogressOrders[0].status);
        }
    });
    let tableId = document.querySelector('#ordertable');
    if (inprogressOrders === 'undefined' || inprogressOrders.length === 0){
        tableId.innerHTML = "";
        orderMessageId.innerHTML = selectedUser;
        console.log(" no order inside table area : ");
        orderArea.querySelector("#order-info").classList.remove("active");
    }else{
        tableId.innerHTML = "";
        orderArea.querySelector("#order-info").classList.add("active");
        console.log("inside table area : ");
        let tr1 = document.createElement('tr');
        tr1.innerHTML = "<th>Order ID</th><th>Size</th><th>Toppings</th><th>Status</th>";
        tableId.append(tr1);
        inprogressOrders.forEach(function(order){
            console.log("making table");
            let tr2 = document.createElement('tr');
            tr2.innerHTML = "<td>"+order.id+"</td><td>"+order.size+"</td><td>"+order.toppings+"</td><td>"+order.status+"</td>";
            tableId.appendChild(tr2);
        });
    }
    inprogressOrders.forEach(function (order){
        console.log("in ack area");    
        if (order['status'] === 'Baked' && order['user_id'] === user_id ){
            console.log("in ack area backed"); 
            document.querySelector("#ackform").classList.add("active");
        }else
            document.querySelector("#ackform").classList.remove("active");
    });
    // remove class "active" from the order-area
    // if selectedUser is "none", just return--nothing to do
    // empty the ordertable, i.e., remove its content: we'll rebuild it
    // add class active to order-area
    // find the user_id of selectedUser via the users array
    // find the in-progress orders for the user by filtering array 
    // orders on user_id and status
    // if there are no orders for user, make ordermessage be "none yet"
    //  and remove active from element id'd order-info
    // Otherwise, add class active to element order-info, make
    //   ordermessage be "", and rebuild the order table 
    // Finally, if there are Baked orders here, make sure that
    // ackform is active, else not active
}

// suggested step 8: have order form hidden until needed
// Let user click on one of two tabs, show its related contents.
// Contents for both tabs are in the HTML after initial setup, 
// but one part is not displayed because of display:none in its CSS
// made effective via class "active".
// Note you need to remove the extra "active" in the originally provided
// HTML near the comment "active here to make everything show"
function setupTabs() {
    console.log("starting setupTabs");
    document.querySelectorAll(".tabs a span").forEach(function (element) {
    element.addEventListener("click", function () {
        event.preventDefault();
        document.querySelectorAll(".tabs span").forEach(function (element) {
            element.classList.remove("active");
        });
        element.classList.add("active");
        console.log("TAB clicked: " + element.innerHTML);
        if (element.parentElement.matches(":nth-child(1)")) {
            console.log("welcome student");
            document.querySelectorAll(".tabContent")[0].classList.add("active");
            document.querySelectorAll(".tabContent")[1].classList.remove("active");
            
        } else if(element.parentElement.matches(":nth-child(2)")){
            console.log("order form");
            document.querySelectorAll(".tabContent")[1].classList.add("active");
            document.querySelectorAll(".tabContent")[0].classList.remove("active");
        }
    });
    });
    document.querySelector(".tabs a:first-child span").click();
    
    
    // Do this last. You may have a better approach, but here's one
    // way to do it. Also edit the html for better initial settings
    // of class active on these elements.    
    // Find an array of span elements for the tabs and another
    //  array of elements with class tabContent, the content for each tab.
    // Then tabSpan[0] is the first span and tabContent[0] is the
    // corresponding contents for that tab, and similarly with [1]s.
    // Then loop through the two cases i=0 and i=1:
    //   loop through tabSpan removing all class active's
    //   loop through tabContents removing all class active's
    //   set tabSpan[i]'s element active
    //   set tabContent[i]'s element active
}

// suggested step 5
function displaySizesToppingsOnOrderForm() {
    console.log("displaySizesToppingsOnOrderForm");
    let orderSizeId = document.querySelector("#order-sizes");
    sizes.forEach(function (size) {
        let input = document.createElement('input');
        let label = document.createElement('label');
        input.type = "radio";
        input.name = "orderSize";
        input.value = size['size'];
        orderSizeId.append(input);
        label.innerHTML = size['size'];
        orderSizeId.append(label);         
    });
    let orderToppingsId = document.querySelector("#order-toppings");
    toppings.forEach(function (top) {
        let input = document.createElement('input');
        let label = document.createElement('label');
        input.type = "checkbox";
        input.value = top['topping'];
        input.name = "orderToppings[]";
        orderToppingsId.append(input);
        //orderToppings.push(input.value);
        label.innerHTML = top['topping'];
        orderToppingsId.append(label);
     });
    // find the element with id order-sizes, and loop through sizes,
    // setting up <input> elements for radio buttons for each size
    // and labels for them too // and for each topping create an <input> element for a checkbox
    // and a <label> for each
}

// suggested step 6, and needs post_order
function setupOrderForm() {
    console.log("setupOrderForm...");
    let formsubmitbtnId = document.querySelector("#orderform .submitbutton");
    orderToppings = [];
    let sizeName;
    let userId;
    let orderToAdd =[];
    let orderId;
    let onSuccess = true;
    
    formsubmitbtnId.addEventListener("click",event => {
        console.log("submit pressed : ");
        
        let orderToppingsId = document.querySelector("#order-toppings");
        let toppingElements = document .querySelectorAll('#order-toppings input[type=checkbox]:checked');
        for(let i=0;i<toppingElements.length;i++){
            orderToppings.push(toppingElements[i].value);
        }
        let orderSizeId = document.querySelector("#order-sizes input[type=radio]:checked");
        let orderMessageId = document.querySelector("#order-message");
        if(orderSizeId.value != '' && orderToppingsId.value != ''){
            sizeName = orderSizeId.value;
            console.log("size added :"+sizeName);
            console.log("toppings added :"+orderToppings);
            userId = findUserId(selectedUser);
            //orderToAdd = [{user_id:"2",size:"Large",day:"1",status:"Baked",toppings:["Onions"]}];
            orderToAdd = [{user_id:userId,size:sizeName,day : currentDay,status : "Preparing",toppings :orderToppings}];
            console.log("order to be aded : " + orderToAdd);
            postOrder(orderToAdd[0],onSuccess);
            console.log("order to be aded with id : " + lastOrderId);
            if(onSuccess){
                orderMessageId.innerHTML="order added with id : " +lastOrderId;
            }
        }else{
            orderMessageId.innerHTML="Invalid values of toppings or sizes";
        }
        displayOrders();
        document.querySelectorAll(".tabContent")[0].classList.add("active");
        document.querySelectorAll(".tabContent")[1].classList.remove("active");
        //event.preventDefault();
    });
    
    // find the orderform's submitbutton and put an event listener on it
    // When the click event comes in, figure out the sizeName from
    // the radio button and the toppings from the checkboxes
    // Complain if these are not specified, using order-message
    // Else, figure out the user_id of the selectedUser, and
    // compose an order, and post it. On success, report the
    // new order number to the user using order-message
}

// Plain modern JS: use fetch, which returns a "promise"
// that we can combine with other promises and wait for all to finish
function getSizes() {
    let promise = fetch(
            baseUrl + "/sizes",
            {method: 'GET'}
    )
            .then(// fetch sucessfully sent the request...
                    response => {
                        if (response.ok) { // check for HTTP 200 responsee
                            //  Need the "return" keyword in the following--
                            return response.json();
                        } else {  // throw to .catch below
                            throw Error('HTTP' + response.status + ' ' +
                                    response.statusText);
                        }
                    })
            .then(json => {
                console.log("back from fetch: %O", json);
                sizes = json;
            })
            .catch(error => console.error('error in getSizes:', error));
    return promise;
}

function getToppings() {
    let promise = fetch(
            baseUrl + "/toppings",
            {method: 'GET'}
    )
            .then(// fetch sucessfully sent the request...
                    response => {
                        if (response.ok) { // check for HTTP 200 responsee
                            return response.json();
                        } else {  // throw to .catch below
                            throw Error('HTTP' + response.status + ' ' +
                                    response.statusText);
                        }
                    })
            .then(json => {
                console.log("back from fetch: %O", json);
                toppings = json;
            })
            .catch(error => console.error('error in getToppings:', error));
    return promise;
}

function getUsers() {
    let promise = fetch(
            baseUrl + "/users",
            {method: 'GET'}
    )
            .then(// fetch sucessfully sent the request...
                    response => {
                        if (response.ok) { // check for HTTP 200 responsee
                            return response.json();
                        } else {  // throw to .catch below
                            throw Error('HTTP' + response.status + ' ' +
                                    response.statusText);
                        }
                    })
            .then(json => {
                console.log("back from fetch: %O", json);
                users = json;
            })
            .catch(error => console.error('error in getUsers:', error));
    return promise;
}

function getOrders() {
    let promise = fetch(
            baseUrl + "/orders",
            {method: 'GET'}
    )
            .then(// fetch sucessfully sent the request...
                    response => {
                        if (response.ok) { // check for HTTP 200 responsee
                            return response.json();
                        } else {  // throw to .catch below
                            throw Error('HTTP' + response.status + ' ' +
                                    response.statusText);
                        }
                    })
            .then(json => {
                console.log("back from fetch: %O", json);
                orders = json;
            })
            .catch(error => console.error('error in getOrders:', error));
    return promise;
}

function getDay() {
    let promise = fetch(
            baseUrl + "/day",
            {method: 'GET'}
    )
            .then(// fetch sucessfully sent the request...
                    response => {
                        if (response.ok) { // check for HTTP 200 responsee
                            return response.json();
                        } else {  // throw to .catch below
                            throw Error('HTTP' + response.status + ' ' +
                                    response.statusText);
                        }
                    })
            .then(json => {
                console.log("back from fetch: %O", json);
                currentDay = json;
            })
            .catch(error => console.error('error in get Day:', error));
    return promise;
}

function updateOrder(order) {
    let promise = fetch(
            baseUrl + "/orders/" + order['id'],
            {method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(order),} 
    )
    .then(// fetch sucessfully sent the request...
                    response => {
                        if (response.ok) { // check for HTTP 200 responsee
                            return response.json();
                        } else {  // throw to .catch below
                            throw Error('HTTP' + response.status + ' ' +
                                    response.statusText);
                        }
                    })
            .then(json => {
                console.log("back from fetch: %O", json);
                //orders = json;
            })
            .catch(error => console.error('error in updateOrder:', error));
}
function postOrder(order, onSuccess) {
    let promise = fetch(baseUrl + "/orders", {
            method: 'POST', // or 'PUT'
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify(order),
    })
    .then(response => response.json())
    .then(json => {
      console.log('Success order:', json);
      orders = json;
      lastOrderId = json['id'];
    })
    .catch((error) => {
      onSuccess = false;
      console.error('Error in post:', error);
    });
    return promise;
}
function refreshData(thenFn) {
    // wait until all promises from fetches "resolve", i.e., finish fetching
    Promise.all([getSizes(), getToppings(),getDay(), getUsers(), getOrders()]).then(thenFn);
}

console.log("starting...");
refreshData(main);
