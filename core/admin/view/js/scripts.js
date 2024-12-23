document.querySelector('.sitemap-button').onclick = (e) => {

    e.preventDefault();

    createSitemap();

}

let links_counter = 0;

function createSitemap(){

    links_counter++;
    
    Ajax({data: {ajax:'sitemap', links_counter: links_counter}})
       .then((res) => {
          console.log('успех - ' + res);
       })
       .catch((res) => {
          console.log('ошибка - ' + res);
       });

}

createFile();

function createFile(){

    let files = document.querySelectorAll('input[type=file]');

    let fileStore = [];

    if (files.length){

        files.forEach(item => {

            item.onchange = function (){

                let multiple = false;

                let parentContainer;

                let container;

                if (item.hasAttribute('multiple')){

                    multiple = true;

                    parentContainer = this.closest('.gallery_container')

                    if (!parentContainer) return false;

                    container = parentContainer.querySelectorAll('.empty_container');

                    if (container.length < this.files.length){

                        for (let index = 0; index < this.files.length - container.length; index++){

                            let  el = document.createElement('div')

                            el.classList.add('vg-dotted-square', 'vg-center', 'empty_container')

                            parentContainer.append(el)
                        }

                        container = parentContainer.querySelectorAll('.empty_container');

                    }

                }

                let  fileName = item.name;

                let  attributeName = fileName.replace(/[\[\]]/g, '');

                for (let  i in this.files){

                    if (this.files.hasOwnProperty(i)){

                        if (multiple){

                            if (typeof fileStore[fileName] === 'undefined') fileStore[fileName] = [];

                            let elId = fileStore[fileName].push(this.files[i]) - 1;

                            container[i].setAttribute(`data-deleteFileId-${attributeName}`, elId);

                            showImage(this.files[i], container[i], function (){

                                parentContainer.sortable({excludedElements: 'label .empty_container'})

                            });

                            deleteNewFiles(elId, fileName, attributeName, container[i]);

                        }else{

                            container = this.closest('.img_container').querySelector('.img_show');

                            showImage(this.files[i], container);

                        }

                    }

                }

            }

            let area = item.closest('.img_wrapper');

            if (area){

                dragAndDrop(area, item);

            }

        });

        let form = document.querySelector('#main-form');

        if (form){

            form.onsubmit = function(e){

                createjsSortable(form);

                if(!isEmpty(fileStore)){

                    e.preventDefault();

                    let forData = new FormData(this);

                    for(let  i in fileStore){

                        if (fileStore.hasOwnProperty(i)){

                            forData.delete(i);

                            let  rowName = i.replace(/[\[\]]/g, '');

                            fileStore[i].forEach((item, index) => {

                                forData.append(`${rowName}[${index}]`, item);

                            });

                        }

                    }

                    forData.append('ajax', 'editData')

                    Ajax({
                        url: this.getAttribute('action'),
                        type: 'post',
                        data: forData,
                        processData: false,
                        contentType: false
                    }).then(res => {

                        try {

                            res = JSON.parse(res);

                            if (!res.success) throw  new Error();

                            location.reload();

                        }catch (e){

                            alert('Произошла внутренняя ошибка')

                        }

                    });

                }

            }

        }

        function  deleteNewFiles(elId, fileName, attributeName, container){

            container.addEventListener('click', function (){

                this.remove();

                delete fileStore[fileName][elId];

            });
        }

        function  showImage(item, container, calcback){

            let  reader = new FileReader();

            container.innerHTML = '';

            reader.readAsDataURL(item);

            reader.onload = e => {

                container.innerHTML = '<img class="img_item" src="">';

                container.querySelector('img').setAttribute('src', e.target.result);

                container.classList.remove('empty_container');

                calcback && calcback()
            }

        }

        function dragAndDrop(area, input){

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName, index) => {

                area.addEventListener(eventName, e => {

                    e.preventDefault();

                    e.stopPropagation();

                    if (index < 2){

                        area.style.background = 'lightblue';

                    }else{

                        area.style.background = '#fff';

                        if (index === 3){

                            input.files = e.dataTransfer.files;

                            input.dispatchEvent(new Event('change'));

                        }

                    }

                });

            });

        }

    }

}

chengeMenuPosition()

function chengeMenuPosition(){

    let  form = document.querySelector('#main-form');

    if (form){

        let selectParent = form.querySelector('select[name=parent_id]');

        let selectPosition = form.querySelector('select[name=menu_position]');

        if (selectPosition && selectParent){

            let defaultParent = selectParent.value;

            let defaultPosition = +selectPosition.value;

            selectParent.addEventListener('change', function (){

                let defaultChoose = false;

                if (this.value === defaultParent) defaultChoose = true;

                Ajax({
                    data: {
                        table: form.querySelector('input[name=table]').value,
                        'parent_id': this.value,
                        ajax: 'chenge_parent',
                        iteration:! form.querySelector('#tableId') ? 1 : +!defaultChoose
                    }
                }).then(res => {

                    res = +res;

                    if (!res) return errorAlert();

                    let newSelect = document.createElement('select');

                    newSelect.setAttribute('name', 'menu_position');

                    newSelect.classList.add('vg-input', 'vg-text', 'vg-full', 'vg-firm-color1')

                    for (let i = 1; i <= res; i++){

                        let selected = defaultChoose && i === defaultPosition ? 'selected' : '';

                        newSelect.insertAdjacentHTML("beforeend", `<option ${selected} value="${i}">${i}</option>>`);

                    }

                    selectPosition.before(newSelect);

                    selectPosition.remove();

                    selectPosition = newSelect

                })
            });

        }

    }

}

blockPaarametrs();

function blockPaarametrs(){

    let wraps = document.querySelectorAll('.select_wrap');

    if (wraps.length){

        let selectAllIndexes = [];

        wraps.forEach(item => {

            let next = item.nextElementSibling;

            if (next && next.classList.contains('option_wrap')){

                item.addEventListener('click', e => {

                    if (!e.target.classList.contains('select_all')){

                        next.slideToggle();

                    }else{

                        let index = [...document.querySelectorAll('.select_all')].indexOf(e.target);

                        if (typeof selectAllIndexes[index] === 'undefined') selectAllIndexes[index] = false;

                        selectAllIndexes[index] = !selectAllIndexes[index];

                        next.querySelectorAll('input[type=checkbox]').forEach(el => el.checked = selectAllIndexes[index]);
                        
                    }

                });

            }

        });

    }

}

showHideMenuSearch()

function showHideMenuSearch(){

    document.querySelector('#hideButton').addEventListener('click', () => {

        document.querySelector('.vg-carcass').classList.toggle('vg-hide')

    });

    let searchBtn = document.querySelector('#searchButton');

    let searchInput = searchBtn.querySelector('input[type=text]');

    searchBtn.addEventListener('click', () => {

        searchBtn.classList.add('vg-search-reverse');

        searchInput.focus();

    });

    searchInput.addEventListener('blur', e => {

        if (e.relatedTarget && e.relatedTarget.tagName === 'A')
            return

        searchBtn.classList.remove('vg-search-reverse');

    })

}

let searchResultHover = (() => {

    let searchRes = document.querySelector('.search_res');

    let searchInput = document.querySelector('#searchButton input[type=text]');

    let defaultInputValue = null;

    function  searchKeyDown(e){

        if (!(document.querySelector('#searchButton').classList.contains('vg-search-reverse')) ||
            (e.key !== 'ArrowUp' && e.key !== 'ArrowDown')) return;

        let children = [...searchRes.children];

        if (children.length){

            e.preventDefault();

            let  activeItem = searchRes.querySelector('.search_act');

            let  activeIndex = activeItem ? children.indexOf(activeItem) : -1;

            if (e.key !== 'ArrowUp')
                activeIndex = activeIndex <= 0 ? children.length - 1 : --activeIndex;
            else
                activeIndex = activeIndex === children.length - 1 ? 0 : ++activeIndex;

            children.forEach(item => item.classList.remove('search_act'));

            children[activeIndex].classList.add('search_act');

            searchInput.value = children[activeIndex].innerText.replace(/\(.+?\)s*$/, '');
        }

    }

    function setDefaultValue(){

        searchInput.value = defaultInputValue;

    }

    searchRes.addEventListener('mouseleave', setDefaultValue)

    window.addEventListener('keydown', searchKeyDown)

    return () => {

        defaultInputValue = searchInput.value

        if (searchRes.children.length){

            let children = [...searchRes.children]

            children.forEach(item => {

                item.addEventListener('mouseover', () => {

                    children.forEach(el => el.classList.remove('search_act'))

                    item.classList.add('search_act')

                    searchInput.value = item.innerText

                })

            })

        }

    }

})();

searchResultHover();

search()

function search(){

    let searchInput = document.querySelector('input[name=search]');

    if (searchInput){

        searchInput.oninput = () => {

            if (searchInput.value.length > 1){

                Ajax(
                    {
                        data:{
                            data: searchInput.value,
                            table: document.querySelector('input[name="search_table"]').value,
                            ajax: 'search'
                        }
                    }
                ).then(res => {

                    console.log(res);

                    try {

                        res = JSON.parse(res)

                        let resBlok = document.querySelector('.search_res');

                        let counter = res.length > 20 ? 20 : res.length

                        if (resBlok){

                            resBlok.innerHTML = '';

                            for (let i = 0; i < counter; i++){

                                resBlok.insertAdjacentHTML('beforeend', `<a href="${res[i]['alias']}">${res[i]['name']}</a>`)

                            }

                            searchResultHover();

                        }

                    }catch (e){

                        alert('Ошибка поиска по админ панели')

                    }

                });

            }

        }

    }

}



let galleries = document.querySelectorAll('.gallery_container');

if (galleries.length){

    galleries.forEach(item => {

        item.sortable( {
            excludedElements: 'label .empty_container',
            stop: function (dragEl){}
        })

    })

}

function createjsSortable(form){

    if (form){

        let sortable = form.querySelectorAll('input[type=file][multiple]');

        if (sortable.length){

            sortable.forEach(item =>  {

                let container = item.closest('.gallery_container');

                let name = item.getAttribute('name');

                if (name && container){

                    name = name.replace(/\[\]/g, '');

                    let inputSorting = form.querySelector(`input[name="js-sorting[${name}]"]`);

                    if (!inputSorting){

                        inputSorting = document.createElement('input');

                        inputSorting.name = `js-sorting[${name}]`;

                        form.append(inputSorting);

                    }

                    let res = [];

                    for (let i in container.children){

                        if (container.children.hasOwnProperty(i)){

                            if (!container.children[i].matches('label') && !container.children[i].matches('empty_container')){

                                if (container.children[i].tagName === 'A'){

                                    res.push(container.children[i].querySelector('img'). getAttribute('src'));

                                }else {

                                    res.push(container.children[i].getAttribute(`data-deletefileid-${name}`))

                                }

                            }

                        }

                    }
                    console.log(res);
                    inputSorting.value = JSON.stringify(res);

                }

            });

        }

    }

}

document.addEventListener('DOMContentLoaded', () => {

    function hideMessages(){

        document.querySelectorAll('.success, .error').forEach(item => item.remove());

        console.log('remove')
        document.removeEventListener('click', hideMessages);

    }

    document.addEventListener('click', hideMessages);

});