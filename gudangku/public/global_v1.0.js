const getDateToContext = (datetime, type) => {
    if(datetime){
        const result = new Date(datetime);

        if (type == "full") {
            const now = new Date(Date.now());
            const yesterday = new Date();
            const tomorrow = new Date();
            yesterday.setDate(yesterday.getDate() - 1);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            if (result.toDateString() === now.toDateString()) {
                return ` ${messages('today_at')} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`;
            } else if (result.toDateString() === yesterday.toDateString()) {
                return ` ${messages('yesterday_at')} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`;
            } else if (result.toDateString() === tomorrow.toDateString()) {
                return ` ${messages('tommorow_at')} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`;
            } else {
                return ` ${result.getFullYear()}/${(result.getMonth() + 1)}/${("0" + result.getDate()).slice(-2)} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`;
            }
        } else if (type == "24h" || type == "12h") {
            return `${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`;
        } else if (type == "datetime") {
            return ` ${result.getFullYear()}/${(result.getMonth() + 1)}/${("0" + result.getDate()).slice(-2)} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`;
        } else if (type == "date") {
            return `${result.getFullYear()}-${("0" + (result.getMonth() + 1)).slice(-2)}-${("0" + result.getDate()).slice(-2)}`;
        } else if (type == "calendar") {
            const result = new Date(datetime);
            const offsetHours = getUTCHourOffset();
            result.setUTCHours(result.getUTCHours() + offsetHours);
        
            return `${result.getFullYear()}-${("0" + (result.getMonth() + 1)).slice(-2)}-${("0" + result.getDate()).slice(-2)} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}:00`;
        }        
    } else {
        return "-"
    }
}

const getUTCHourOffset = () => {
    const offsetMi = new Date().getTimezoneOffset();
    const offsetHr = -offsetMi / 60;
    return offsetHr;
}

const getUUID = () => {
    return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
        (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
    );
}

const validateInput = (type, id, max, min) => {
    if(type == "text"){
        const check = $(`#${id}`).val()
        const check_len = check.trim().length
    
        if(check && check_len > 0 && check_len <= max && check_len >= min){
            return true
        } else {
            return false
        }
    }
}

const ucEachWord = (val) => {
    const arr = val.split(" ")
    for (var i = 0; i < arr.length; i++) {
        arr[i] = arr[i].charAt(0).toUpperCase() + arr[i].slice(1)
    }
    const res = arr.join(" ")

    return res
}

const ucFirst = (val) => {
    if (typeof val !== 'string' || val.length === 0) {
        var res = val
    } else {
        var res = val.charAt(0).toUpperCase() + val.slice(1)
    }

    return res
}

const generate_pagination = (items_holder, fetch_callback, total_page, current_page) => {
    let page_element = ''
    for (let i = 1; i <= total_page; i++) {
        page_element += `
            <a class='btn-page ${i === current_page ? 'active' : ''}' href='#' data-page='${i}' title='Open page: ${i}'>${i}</a>
        `
    }

    $(`#pagination-${items_holder}`).remove()
    $(`<div id='pagination-${items_holder}'><label>Page</label>${page_element}</div>`).insertAfter(`#${items_holder}`)
    $(document).off('click', `#pagination-${items_holder} .btn-page`)
    $(document).on('click', `#pagination-${items_holder} .btn-page`, function() {
        const selectedPage = $(this).data('page')
        fetch_callback(selectedPage)
    });
};