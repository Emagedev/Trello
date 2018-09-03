# Connect Your Magento&trade; 1.9 Orders With Trello&trade; Board

## Control Your Orders Statuses With Trello&trade;
This module can help you to view your order's statuses realtime with Trello&trade; board updates. 
All you need to do is to set up your API key & token and grab a board ID.
You can read how to set up module [here](#setUp).

**As for now, you cannot control your Magento&trade; orders using Trello&trade; Board.**

_It will possibly be added in near future as we plan to do this for our store._

Also, this module provide simple API methods to interact with Trello&trade;. If you want to know more, see [here](#api).

## <a href="#setUp" name="setUp">#</a> Setting Up

To set up a module, you should log in into your admin panel (if you already logged in, you probably should log out first). 
Then go to system → configuration. In sidebar, find Trello API under services tab.

To set up token and access key, go to <a href="https://trello.com/app-key" target="_blank">https://trello.com/app-key</a>,
copy a key ang generate token (there's a link under token description).
Paste key and token to the corresponding fields.

Then, create or open in browser one of existing Trello&trade; boards, click the "show menu" button
if menu not opened, click "More" → "Print and Export" and then select "Export as JSON".
The JSON document will be opened in your browser, you need to copy the value of id field, it is like
`id: "0123456789abcdef12345678"`, then paste that id (like `0123456789abcdef12345678`) into
Board ID field inside Order Status box.


## <a href="#api" name="api">#</a> API Methods
<a href="#createCard" name="createCard">#</a> Mage::getSingleton('trello')-><b>createCard($params)</b>

Creates a card with <a href="#cardParams">following params</a>.

Called API method: https://developers.trello.com/v1.0/reference#cards-2

<a href="#updateCard" name="updateCard">#</a> Mage::getSingleton('trello')-><b>updateCard($cardId, $params)</b>

Updates a card with cardId with <a href="#cardParams">following params</a>.

Called API method: https://developers.trello.com/v1.0/reference#cards-1

## <a href="#cardParams" name="cardParams">#</a> Card Params
Params should be passed as associative array with following keys to method:

* `name` `(string)` Card name
* `desc` `(string)` Card detailed description in full view
* `closed` Is card makred as closed
* `idMembers` List of members that connected to this card
* `idAttachmentCover` Id of attachment that used as card cover
* `idList` Id of list to which card belongs
* `idLabels` Id of labels (tags) for this card
* `idBoard` Id of board on which card should appear
* `pos` Card position in list
* `due` Due date
* `dueComplete` Mark due date as complete
* `subscribed` 

---

### Translations

Packaged with american english (en_US) and russian (ru_RU) translations.

### Unit Testing

Most of API methods are covered, as well as order helpers. Unit tests provided by EcomDev_PHPUnit.

### Known Issues

* Sometimes order card may not be created or updated, because we hardly limit execution time to keep your store fast. This probably will be fixed in near future, 
as we release a scheduling module for queues. 

### To Do

* Add dependency for scheduled running of API calls, with TTL and retries
* Maybe using RabbitMQ
* Opposite direction API - update orders when cards updated 

---

> **N.B. You can update all cards at any time using action in admin order grid.**