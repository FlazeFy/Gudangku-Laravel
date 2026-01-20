const statsFetchRestTime = 120

const failedMessage = (context) => {
    Swal.fire("Oops!", `Failed to ${context}`, "error")
}

const isMobile = () => {
    const key = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i
    return key.test(navigator.userAgent)
}

const closeModalBS = () => {
    $('.modal.show').each(function () {
        bootstrap.Modal.getInstance(this)?.hide()
    }) 
}

const getDateToContext = (datetime, type, isFlexible = true) => {
    if(datetime){
        const result = new Date(datetime)

        if (type == "full") {
            const now = new Date(Date.now())
            const yesterday = new Date()
            const tomorrow = new Date()
            yesterday.setDate(yesterday.getDate() - 1)
            tomorrow.setDate(tomorrow.getDate() + 1)
            
            if (result.toDateString() === now.toDateString()) {
                return ` ${messages('today_at')} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`
            } else if (result.toDateString() === yesterday.toDateString()) {
                return ` ${messages('yesterday_at')} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`
            } else if (result.toDateString() === tomorrow.toDateString()) {
                return ` ${messages('tommorow_at')} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`
            } else {
                return ` ${result.getFullYear()}/${(result.getMonth() + 1)}/${("0" + result.getDate()).slice(-2)} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`
            }
        } else if (type == "24h" || type == "12h") {
            return `${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`
        } else if (type == "datetime") {
            return ` ${result.getFullYear()}/${(result.getMonth() + 1)}/${("0" + result.getDate()).slice(-2)} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`
        } else if (type == "date") {
            return `${result.getFullYear()}-${("0" + (result.getMonth() + 1)).slice(-2)}-${("0" + result.getDate()).slice(-2)}`
        } else if (type == "calendar") {
            if(isFlexible){
                const offsetHours = getUTCHourOffset()
                result.setUTCHours(result.getUTCHours() + offsetHours)
            }
        
            return `${result.getFullYear()}-${("0" + (result.getMonth() + 1)).slice(-2)}-${("0" + result.getDate()).slice(-2)} ${("0" + result.getHours()).slice(-2)}:${("0" + result.getMinutes()).slice(-2)}`
        }        
    } else {
        return "-"
    }
}

const getUTCHourOffset = () => {
    const offsetMi = new Date().getTimezoneOffset()
    const offsetHr = -offsetMi / 60

    return offsetHr
}

const getUUID = () => {
    return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
        (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
    )
}

const validateInput = (type, id, max, min) => {
    if(type == "text"){
        const check = $(`#${id}`).val()
        const checkLen = check.trim().length
    
        if(check && checkLen > 0 && checkLen <= max && checkLen >= min){
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

const generatePagination = (items_holder, fetch_callback, total_page, current_page) => {
    let page_element = ''
    for (let i = 1; i <= total_page; i++) {
        page_element += `<a class='btn-page ${i === current_page ? 'active' : ''}' href='#' data-page='${i}' title='Open page: ${i}'>${i}</a>`
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
    })
}

const generateAPIError = (response, is_list_format) => {
    Swal.close()
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
        })
    } else if(response.status === 404){
        Swal.fire("Oops!", "Data not found","error")
    } else {
        Swal.fire("Oops!", response.responseJSON?.message || "Something went wrong", "error")
    }
}

const generateEmptyFieldError = (context) => {
    Swal.fire("Oops!", `You must select the ${context}`, "warning")
}

const generateLastPageError = () => {
    Swal.fire("Oops!", "You are at the last page", "warning")
}

const checkFillingStatus = (list) => {
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
    })
}

const getReminderContext = (reminderType) => {
    switch (reminderType) {
        case 'Every Day': {
            // Hours: 00–23
            return Array.from({ length: 24 }, (_, i) => `Every ${String(i).padStart(2, '0')}`)
        }
        case 'Every Week': {
            return ['Every Monday','Every Tuesday','Every Wednesday','Every Thursday','Every Friday','Every Saturday','Every Sunday']
        }
        case 'Every Month': {
            // Days: 01–28
            return Array.from({ length: 28 }, (_, i) => `Every ${String(i + 1).padStart(2, '0')}`)
        }
        case 'Every Year': {
            const months = ['January','February','March','April','May','June','July','August','September','October','November','December']

            const result = []
            for (let m of months) {
                for (let d = 1; d <= 28; d++) {
                    result.push(`Every ${String(d).padStart(2, '0')} ${m}`)
                }
            }
            return result
        }
        default:
            return []
    }
}

const getReminderContextSelect = (reminderType, target, selected = null) => {
    const $target = target instanceof jQuery ? target : $(target)

    $target.empty().append(`<option>-</option>`)
    const list = getReminderContext(reminderType)

    list.forEach(dt => {
        $target.append(`<option value="${dt}" ${selected === dt ? "selected":""}>${dt}</option>`)
    })
}

const countTime = (date1, date2) => {
    const oneHour = 60 * 60 * 1000
    const oneDay = 24 * oneHour
    const firstDate = new Date(date1)
    const secondDate = new Date(date2 ?? Date.now())
    const diffInMilliseconds = Math.abs(secondDate - firstDate)
  
    if (diffInMilliseconds >= oneDay) {
        const days = Math.floor(diffInMilliseconds / oneDay)
        return `${days} day${days > 1 ? 's' : ''} ago`
    } else if (diffInMilliseconds >= oneHour) {
        const hours = Math.floor(diffInMilliseconds / oneHour)
        return `${hours} hour${hours > 1 ? 's' : ''} ago`
    } else {
        return 'now'
    }
}

const checkAll = (target,type) => {
    $(target).prop('checked', type == 'check' ? true : false)
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
                                <img class="img img-fluid d-block mx-auto" style="border-radius: var(--roundedMD)" src="${url}" title="${url}">
                            </div>
                        </div>
                    </div>
                </div>
            `)
        }
    })
}

const setArrowCollapse = () => {
    $(document).ready(function () {
        $('[data-bs-toggle="collapse"]').each(function () {
            const collapseElement = $(this).closest('div').find('.collapse')
            const direction = collapseElement.hasClass('show') ? 'up' : 'down'
            $(this).html(`<i class="fa-solid fa-circle-chevron-${direction}" style="transition: transform 0.3s"></i> ${$(this).text()}`)

            $(this).on('click', function () {
                const icon = $(this).find('i')
                const isOpen = collapseElement.hasClass('show')
                closed_control = isOpen ? false : true

                const currentRotation = icon.data('rotation') || 0
                const newRotation = currentRotation + 180
                icon.css('transform',`rotate(${newRotation}deg)`)
                icon.data('rotation', newRotation)
            })
        })
    })
}
setArrowCollapse()

const formValidation = (context) => {
    $(document).ready(function() {
        $('.form-validated').each(function(idx, el) {
            if ($(this).is('input, textarea')) {
                if ($(this).attr('name')) {
                    const name = ucEachWord($(this).attr('name').trim().replaceAll('_',' '))
                    const type = $(this).attr('type')
                    const isRequired = $(this).attr('required') === undefined ? false : true
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

                    $(this).before(`<label title='This input is ${isRequired ? 'mandatory' : 'optional'}${lengthTitle}'>${isRequired == true ? `<span class='text-danger'>*</span>`:''}${name.replace(context,'')}</label>`)

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
                            $(`#alert-holder-${idx}-${$(this).attr('name')}`).html(`<label class='text-danger fst-italic' style='font-size:12px'><i class="fa-solid fa-triangle-exclamation"></i> ${lengthWarning}</label><br>`)
                            $(this).css('border','2px solid var(--dangerBG)')
                        } else {
                            $(`#alert-holder-${idx}-${$(this).attr('name')}`).empty()
                            $(this).css('border','1.5px solid var(--primaryColor)')
                        }
                    })                  
                } else {
                    alert(`Can't validate a form with index - ${idx}: No name attribute`)
                }
            } else {
                alert(`Can't validate a form with index - ${idx} : Not valid form validation`)
            }
        })
    })
}

const setCurrentLocalDateTime = (target) => {
    const now = new Date()
    const year = now.getFullYear()
    const month = String(now.getMonth() + 1).padStart(2, "0")
    const day = String(now.getDate()).padStart(2, "0")
    const hour = String(now.getHours()).padStart(2, "0")
    const minute = String(now.getMinutes()).padStart(2, "0")
    const formatted = `${year}-${month}-${day}T${hour}:${minute}`

    $(`#${target}`).val(formatted)
}

const tidyUpDateTimeFormat = (datetime) => {
    datetime = new Date(datetime)
    const year = datetime.getFullYear()
    const month = String(datetime.getMonth() + 1).padStart(2, '0')
    const day = String(datetime.getDate()).padStart(2, '0')
    const hour = String(datetime.getHours()).padStart(2, '0')
    const minute = String(datetime.getMinutes()).padStart(2, '0')

    return `${year}-${month}-${day} ${hour}:${minute}:00`
}