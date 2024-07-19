
function populateSelect(element, options, selected_id = "") {
    $(`${element}`).empty().append(` <option value="" > Choose an option </option> `);
    options.forEach(option => {
        let selected = option.id == selected_id ? "selected" : "";
        $(`${element}`).append(` <option ${selected} value="${option.id}" > ${option.title} </option> `);
    });
}