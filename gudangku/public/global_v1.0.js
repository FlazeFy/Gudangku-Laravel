const statsFetchRestTime = 120

const isMobile = () => {
    const key = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i
    
    return key.test(navigator.userAgent)
}

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
        
            return `${result.getFullYear()}-${("0" + (result.getMonth() + 1)).slice(-2)}-${("0" + result.getDate()).slice(-2)} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`;
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
    if ($('#'+items_holder).closest('table').length == 0) {
        $(`<div id='pagination-${items_holder}' class='btn-page-holder'><label>Page</label>${page_element}</div>`).insertAfter(`#${items_holder}`)
    } else {
        $(`<div id='pagination-${items_holder}' class='btn-page-holder'><label>Page</label>${page_element}</div>`).insertAfter($(`#${items_holder}`).closest('table'))
    }
    $(document).off('click', `#pagination-${items_holder} .btn-page`)
    $(document).on('click', `#pagination-${items_holder} .btn-page`, function() {
        const selectedPage = $(this).data('page')
        fetch_callback(selectedPage)
    });
};

const generate_api_error = (response, is_list_format) => {
    if (response.status === 422) {
        let msg = response.responseJSON.message
        
        if(typeof msg != 'string'){
            const allMsg = Object.values(msg).flat()
            if(is_list_format){
                msg = '<ol>'
                allMsg.forEach((dt) => {
                    msg += `<li>- ${dt.replace('.','')}</li>`
                })
                msg += '</ol>'
            } else {
                msg = allMsg.join(', ').replace('.','')
            }
        }

        Swal.fire({
            title: "Validation Error!",
            html: msg,
            icon: "error"
        });
    } else {
        Swal.fire({
            title: "Oops!",
            text: response.responseJSON?.message || "Something went wrong",
            icon: "error"
        });
    }
}

const generate_last_page_error = () => {
    Swal.fire({
        title: "Oops!",
        text: "You are at the last page",
        icon: "warning"
    });
}

const get_dct_by_type = (type) => {
    return new Promise((resolve, reject) => {
        Swal.showLoading();
        $.ajax({
            url: `/api/v1/dictionary/type/${type}`,
            type: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader("Accept", "application/json")
            },
            success: function(response) {
                Swal.close()
                const data = response.data
                let res = []
                data.forEach(dt => {
                    res.push(dt.dictionary_name)
                });
                resolve(res)
            },
            error: function(response, jqXHR, textStatus, errorThrown) {
                Swal.close()
                Swal.fire({
                    title: "Oops!",
                    text: "Failed to fetch dictionary",
                    icon: "error"
                });
                reject(errorThrown)
            }
        });
    });
};

const number_format = (number, decimals, dec_point, thousands_sep) => {
    number = number.toFixed(decimals);

    var nstr = number.split('.');
    var x1 = nstr[0];
    var x2 = nstr.length > 1 ? dec_point + nstr[1] : '';
    
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + thousands_sep + '$2');
    }
    
    return x1 + x2;
}

const check_filling_status = (list) => {
    return list.some((dt) => {
        const el = $(`#${dt}`)
        if(el && el.length > 0){
            const tag = el.prop("tagName").toLowerCase()
            const type = el.attr("type") || null
            
            if (tag === 'input') {
                if ((type === 'text' || type === 'email' || type === 'password') && el.val().trim() !== '') {
                    return true
                }
                if (type === 'checkbox' && el.is(":checked")) {
                    return true
                }
            }
        }
        return false
    });
}

const count_time = (date1,date2,type) => {
    const oneHour = 60 * 60 * 1000
    const oneDay = 24 * oneHour
    const firstDate = new Date(date1)
    const secondDate = new Date(date2 ?? Date.now())
    const diffInMilliseconds = Math.abs(secondDate - firstDate)

    let res = 'invalid type'
    if (type === "day") {
        res = Math.floor(diffInMilliseconds / oneDay)
    } else if (type === "hour") {
        res = Math.floor(diffInMilliseconds / oneHour)
    } 
    return res
}

const check_all = (target,type) => {
    $(target).prop('checked', type == 'check' ? true : false)
    Swal.fire({
        title: "Success!",
        text: `${ucFirst(type)}ed all items`,
        icon: "success"
    });
} 

const zoomableModal = () => {
    $('.img-zoomable-modal').each(function(idx, el) {
        const id = $(this).attr('data-bs-target').replace('#', '')
        const url = $(this).attr('src')

        if (!$(`#${id}`).length) {
            $(this).after(`
                <div class="modal fade" id="${id}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            <div class="modal-body">
                                <img class="img img-fluid d-block mx-auto" style="border-radius: var(--roundedMD);" src="${url}" title="${url}">
                            </div>
                        </div>
                    </div>
                </div>
            `);
        }
    })
}

const setArrowCollapse = () => {
    $(document).ready(function () {
        $('[data-bs-toggle="collapse"]').each(function () {
            const collapseElement = $(this).closest('div').find('.collapse')
            const direction = collapseElement.hasClass('show') ? 'up' : 'down'
            $(this).html(`<i class="fa-solid fa-circle-chevron-${direction}" style="transition: transform 0.3s;"></i> ${$(this).text()}`)

            $(this).on('click', function () {
                const icon = $(this).find('i')
                const isOpen = collapseElement.hasClass('show')
                closed_control = isOpen ? false : true

                const currentRotation = icon.data('rotation') || 0
                const newRotation = currentRotation + 180
                icon.css({ transform: `rotate(${newRotation}deg)` })
                icon.data('rotation', newRotation)
            });
        });
    });
};
setArrowCollapse()

const formValidation = (context) => {
    $(document).ready(function() {
        $('.form-validated').each(function(idx, el) {
            if ($(this).is('input, textarea')) {
                if ($(this).attr('name')) {
                    const name = ucEachWord($(this).attr('name').trim().replaceAll('_',' '))
                    const type = $(this).attr('type')
                    const is_required = $(this).attr('required') === undefined ? false : true
                    let max
                    let min
                    let lengthTitle = ''

                    if(type == 'number'){
                        max = $(this).attr('max') === undefined ? null : $(this).attr('max')
                        min = $(this).attr('min') === undefined ? null : $(this).attr('min')
                    } else {
                        max = $(this).attr('maxlength') === undefined ? null : $(this).attr('maxlength')
                        min = $(this).attr('minlength') === undefined ? null : $(this).attr('minlength')
                    }

                    if(max || min){
                        lengthTitle += '. Have '
                        if(type == 'number'){
                            if(max){
                                lengthTitle += `max value up to ${max}`
                            }
                            if(max && min){ lengthTitle += ' and ' }
                            if(min){
                                lengthTitle += `min value down to ${min}`
                            }
                        } else {
                            if(max){
                                lengthTitle += `max character up to ${max} characters`
                            }
                            if(max && min){ lengthTitle += ' and ' }
                            if(min){
                                lengthTitle += `min character down to ${min} characters`
                            }
                        }
                        $(this).after(`<span id='alert-holder-${idx}-${$(this).attr('name')}'></span>`)
                    }

                    $(this).before(`<label title='This input is ${is_required ? 'mandatory' : 'optional'}${lengthTitle}'>${is_required == true ? `<span class='text-danger'>*</span>`:''}${name.replace(context,'')}</label>`)

                    $(document).on('input', `.form-validated`, function(idx2, el2) {            
                        const val = $(this).val().trim()
                        let lengthWarning = ''
                        
                        if(type == 'number'){
                            if(max && val >= max){
                                lengthWarning = `${name} has reached the maximum value. The limit is ${max}`
                            }
                            if(min && val <= min){
                                lengthWarning = `${name} has reached the minimum value. The limit is ${min}`
                            }
                        } else {
                            if(max && val.length >= max){
                                lengthWarning = `${name} has reached the maximum characters. The limit is ${max} characters`
                            }
                            if(min && val.length <= min){
                                lengthWarning = `${name} has reached the minimum characters. The limit is ${min} characters`
                            }
                        }

                        if(lengthWarning != ''){
                            $(`#alert-holder-${idx}-${$(this).attr('name')}`).html(`<label class='text-danger fst-italic' style='font-size:12px;'><i class="fa-solid fa-triangle-exclamation"></i> ${lengthWarning}</label><br>`)
                            $(this).css({
                                'border':'2px solid var(--dangerBG)'
                            })
                        } else {
                            $(`#alert-holder-${idx}-${$(this).attr('name')}`).empty()
                            $(this).css({
                                'border':'1.5px solid var(--primaryColor)'
                            })
                        }
                    })                  
                } else {
                    alert(`Can't validate a form with index - ${idx}: No name attribute`)
                }
            } else {
                alert(`Can't validate a form with index - ${idx} : Not valid form validation`)
            }
        });
    });
}
