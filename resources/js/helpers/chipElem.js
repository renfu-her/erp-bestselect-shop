module.exports = class ElemChip {
   
    constructor(baseElem, value = '', allValuesObj = null) {
        this.onDelete = null;
        this.onAppend = null;
        this.baseElem = baseElem;
        // init
        this.init(value, allValuesObj);
    }

    init(value, allValuesObj) {
        if (value && allValuesObj) {
            if (typeof value === 'string') { value = value.split(','); }
            value.forEach(item => {
                this.add(item, allValuesObj[item]);
            });
        }
        return value || [];
    }

    add(id, title) {
        let self = this;
        let elem = $(
            '<div class="rounded-pill bg-primary text-white chipElem" id="chip_' +
                id +
                '"></div>'
        );
        let closeBtn = $(
            '<button type="button" target="' +
                id +
                '"class="btn btn-sm btn-light rounded-circle"><i class="bi bi-x-lg"></i></button>'
        ).on("click", onDel);

        elem.append(title);
        elem.append(closeBtn);
        this.baseElem.append(elem);

        function onDel(e) {
            let id = $(this).attr("target");
            $("#chip_" + id).remove();
            if (typeof self.onDelete == "function") {
                self.onDelete(id);
            }
        }
    }

    clear() {
        this.baseElem.html("");
    }
};
