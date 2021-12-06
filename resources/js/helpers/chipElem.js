module.exports = class ElemChip {
   
    constructor(baseElem) {
        this.onDelete = null;
        this.onAppend = null;
        this.baseElem = baseElem;
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
