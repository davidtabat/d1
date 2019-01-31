window.onload = function () {
    initComponent();
};

/**
 * Creates search field
 * 
 * @returns {void}
 */
function createSearchField() {
    var selectInput = getSelectInput(),
            searchInput = document.createElement("input");

    searchInput.id = 'rakuten_category_search_input';
    searchInput.type = 'text';
    searchInput.placeholder = 'Search Rakuten categories';
    searchInput.style.display = 'block';
    searchInput.style.width = '696px';
    searchInput.style.height = '20px';
    searchInput.onkeyup = function () {
        findInSelect(this, selectInput);
    };

    selectInput.parentNode.insertBefore(searchInput, selectInput);
}

/**
 * Modify current select for search purposes
 * 
 * @returns {void}
 */
function modifyCategorySelect() {
    var selectInput = getSelectInput();
    selectInput.size = 7;
    selectInput.style.width = '700px';
    selectInput.style.height = '152px';
    selectInput.childNodes[1].remove();
    selectInput.onchange = function () {
        setSelectedText();
    };
}

/**
 * Finds options that matches searched string
 * 
 * @param {HTML element} searchInput
 * @param {HTML element} selectInput
 * @returns {void}
 */
function findInSelect(searchInput, selectInput) {
    var searchText = searchInput.value.toLowerCase(),
            options = selectInput.childNodes,
            len = options.length;

    for (var i = 0; i < len; i++) {
        if (options[i].text && options[i].text.toLowerCase().indexOf(searchText) === -1) {
            options[i].style.display = 'none';
        }

        if (options[i].text && options[i].text.toLowerCase().indexOf(searchText) > -1) {
            options[i].style.display = '';
        }
    }
}

/**
 * Sets selected option to search field
 * 
 * @returns {void}
 */
function setSelectedText() {
    var selectInput = getSelectInput(),
            searchInput = document.getElementById('rakuten_category_search_input');

    if (selectInput.selectedIndex === -1) {
        return null;
    }

    searchInput.value = selectInput.options[selectInput.selectedIndex].text;
}

function getSelectInput() {
    var selectInput = document.getElementById('rakuten_default_category_id');

    return selectInput;
}

function initComponent() {
    modifyCategorySelect();
    createSearchField();
    setSelectedText();
}