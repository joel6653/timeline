const io = {
    upload(event) {
        switch (event.target.dataset.type) {
            case "csv":
                event.preventDefault();
                fetch("index.php", {method: "POST", body: new FormData(document.getElementById("form_csv"))})
                    .then(response => {return response.json()})
                    .then(data => {
                        memory.data(JSON.stringify(data));
                        table.init(memory.data());
                    });
                document.getElementById("csv").value = "";
                break;
            case "json":
                let file = document.getElementById("json").files[0];
                let reader = new FileReader();
                reader.addEventListener("load", (event) => {
                    const j = JSON.parse(event.target.result);
                    memory.data(JSON.stringify(j.data));
                    memory.options = j.options;
                    memory.sortable = j.sortable;
                    memory.theme(j.theme);
                    memory.title(j.title); // override the default one assigned in the previous statement
                    // todo decode j.options and use them
                    table.init(memory.data());
                });
                reader.readAsText(file);
                document.getElementById("json").value = "";
                break;
        }
    },
    url() {
        const file = location.search.substring(1);
        fetch(file, {method: "GET"})
            .then(response => {return response.json()})
            .then(data => {
                memory.options = data.options;
                memory.sortable = data.sortable;
                memory.theme(data.theme);
                menu.title(data.title);
                table.init(JSON.stringify(data.data));
            });
    },
    download(target) {
        if (memory.fullUI) {
            memory.options = menu.options();
            memory.sortable = menu.sortable();
        }
        const json_string = 
            "{\"data\": " + memory.data() + 
            ", \"title\": \"" + memory.title() + 
            "\", \"options\": " + JSON.stringify(memory.options) + 
            ", \"rows\": " + JSON.stringify(menu.selected_rows()) +
            ", \"sortable\": " + memory.sortable + 
            ", \"theme\": \"" + memory.theme() +
            "\"}";
        const blob = new Blob([json_string], {type: "text/json" });
        target.href = window.URL.createObjectURL(blob);
    }
}

const memory = {
    default_title: "Time Line",
    fullUI: true,
    options: undefined,
    sortable: undefined,
    exists() {
        return localStorage.getItem("data") !== null;
    },
    data(data) {
        if (data === undefined) {
            return localStorage.getItem("data");
        }
        else {
            this.title(this.default_title);
            localStorage.setItem("data", data);
        }
    },
    theme(theme) {
        if (theme === undefined) {
            return document.documentElement.getAttribute("data-bs-theme");
        }
        else {
            document.documentElement.setAttribute("data-bs-theme", theme);
        }
    },
    title(title) {
        if (title === undefined) {
            return localStorage.getItem("title");
        }
        else {
            if (title === null || title.trim() === "") {title = this.default_title;}
            localStorage.setItem("title", title);
            menu.title(title);
        }
    }
}

const menu = {
    all_rows: ".dropdown-item[data-menu='rows']",
    checked_options: ".dropdown-item[data-menu='options'][data-checked='true']",
    checked_rows: ".dropdown-item[data-menu='rows'][data-checked='true']",
    color_picker: document.getElementById("color_picker"),
    custom_title: document.getElementById("custom_title"),
    end_of_row_list: "#view_menu .dropdown-menu",
    page_title: document.getElementById("page_title"),
    save_menu: document.querySelector(".dropdown-item[data-menu='file'][data-item='save']"),
    sort_option: document.querySelector(".dropdown-item[data-menu='options'][data-item='nosort']"),
    unchecked_rows: ".dropdown-item[data-menu='rows'][data-checked='false']",
    view_menu: document.querySelector("#view_menu .dropdown-toggle"),
    add_rows() {
        document.querySelectorAll(this.all_rows).forEach(element => {
            element.remove(); // get rid of menu items left over from a previous chart, if any
        });
        const insertion_point = document.querySelector(this.end_of_row_list);
        table.master.getDistinctValues(0).forEach(category => {
            var badge_count = table.master.getFilteredRows([{column: 0, value: category}]).length.toString();
            
            var li = document.createElement("LI");
            li.setAttribute("class", "position-relative");

            var a = document.createElement("A");
            a.href = "#";
            a.setAttribute("onclick", "menu.toggle(event.target);");
            a.setAttribute("class", "dropdown-item");
            a.dataset.menu = "rows";
            a.dataset.checked = "true";
            a.dataset.label = "Show " + category;
            a.dataset.value = category;
            a.innerText = "Hide " + category;

            var s = document.createElement("SPAN");
            s.setAttribute("class", "badge bg-info rounded-pill");
            s.innerText = badge_count;

            li.append(a);
            li.append(s);

            // want to add them before the divider and reset menu choice at the bottom
            insertion_point.insertBefore(li, insertion_point.children.item(insertion_point.children.length - 2));
        });
    },
    options() {
        const option_object = {};
        if (!timeline.zoomable) {return option_object;} // when making chart from zoomed data, don't respect any of the other options user has set
        option_object["timeline"] = {};
        document.querySelectorAll(this.checked_options).forEach(element => {
            switch (element.dataset.item) {
                case "expand":
                    Object.assign(option_object["timeline"], {colorByRowLabel: true, groupByRowLabel: false});
                    break;
                case "hide":
                    Object.assign(option_object["timeline"], {showRowLabels: false});
                    break;
                case "same":
                    Object.assign(option_object["timeline"], {colorByRowLabel: true});
                    break;
                case "single":
                    Object.assign(option_object["timeline"], {singleColor: this.color_picker.value});
                    break;
                case "sort":
                    break;
                case "wider":
                    option_object["width"] = timeline.element.offsetWidth * 2;
                    break;
            }
        });
        return option_object;
    },
    reset() {
        document.querySelectorAll(this.checked_options).forEach(element => { // set options menu items to default/initial state
            element.innerText = [element.dataset.label, element.dataset.label = element.innerText][0];
            element.dataset.checked = "false";
        });
        document.querySelectorAll(this.unchecked_rows).forEach(element => { // set rows menu items to default/initial state
            element.innerText = [element.dataset.label, element.dataset.label = element.innerText][0];
            element.dataset.checked = "true";
        });
        table.transform();
    },
    selected_rows() {
        const rows = [];
        document.querySelectorAll(this.checked_rows).forEach(element => {
            rows.push(element.dataset.value);
        });
        return rows;
    },
    show() {
        this.title(memory.title());
        // on file menu
        this.save_menu.classList.remove("disabled");
        this.view(true);
    },
    sortable() {
        return this.sort_option.dataset.checked === "false";
    },
    title(title) {
        this.page_title.innerHTML = title;
        this.custom_title.value = title;
    },
    toggle(menu_item) {
        if (menu_item.dataset.menu === "theme") {
            memory.theme(menu_item.dataset.item);
            document.querySelectorAll("[data-menu='theme']").forEach(element => {
                element.classList.toggle("d-none");
            });
            timeline.x_axis(document.querySelector(".offcanvas-body header"));
            timeline.x_axis(timeline.element);
        }
        else {
            menu_item.innerText = [menu_item.dataset.label, menu_item.dataset.label = menu_item.innerText][0]; // swap data-label with innerText
            menu_item.dataset.checked = menu_item.dataset.checked === "false"; // set true to false and false to true
            table.transform(); // make a new timeline with these changed settings in mind
        }
    },
    view(state) {
        const cl = this.view_menu.classList;
        if (state) {
            cl.remove("disabled");
        }
        else {
            cl.add("disabled");
        }
    }
}

const table = {
    master: undefined,
    copy: undefined,
    convert(input) {
        input.forEach(row => {
            var end = row.length - 1; // if 3 columns, end date is 3rd; if 4, end date is 4th (0-based)
            if (!row[end]) { // end date is empty (false)
                row[end] = Date.now(); // so change it to 'now'
            }
        });
        return new google.visualization.arrayToDataTable(input);
    },
    filter() {
        if (!memory.fullUI) {return;} 
        const rows = this.copy.getFilteredRows(
            [{column: 0, test: (value, rowId, columnId, datatable) => {return menu.selected_rows().includes(value);}}]
        );
        const view = new google.visualization.DataView(this.copy);
        view.setRows(rows);
        this.copy = view.toDataTable();
    },
    init(data) { // every time data is found in memory, via File/New, Imported from JSON, etc.
        this.master = this.convert(JSON.parse(data));
        if (memory.fullUI) {
            menu.add_rows();
            menu.reset();
            menu.show();
        }
        this.transform();
    },
    sort(test) {
        if (!test) {return;} // "don't sort" was specified in options
        // either by row label, then start date; or by row label, then start date, then bar label
        const columns = this.copy.getNumberOfColumns() == 3 ? [0, 1] : [0, 2, 1];
        const rows = this.copy.getSortedRows(columns);
        const view = new google.visualization.DataView(this.copy);
        view.setRows(rows);
        this.copy = view.toDataTable();
    },
    transform() {
        this.copy = this.master.clone(); // always use a copy, because some optional timeline actions change the underlying data
        this.filter();
        if (memory.fullUI) {
            memory.options = menu.options();
            memory.sortable = menu.sortable();
        }
        this.sort(memory.sortable);
        // document.documentElement.setAttribute("data-bs-theme")
        timeline.draw(this.copy);
    },
    zoom() {
        const target = timeline.chart.getSelection()[0].row;
        if (isNaN(target)) {return;}
        
        const start_date_column = this.copy.getNumberOfColumns() - 2;
        const end_date_column = start_date_column + 1;
        const target_start = this.copy.getValue(target, start_date_column);
        const target_end = this.copy.getValue(target, end_date_column);
        
        for (let index = 0; index < this.copy.getNumberOfRows(); index++) {
            var current_start = this.copy.getValue(index, start_date_column);
            var current_end = this.copy.getValue(index, end_date_column);
            if (current_start < target_start) {
                if (current_end > target_start) {
                    current_start = target_start;
                }
            }
            if (current_end > target_end) {
                if (current_start < target_end) {
                    current_end = target_end;
                }
            }
            this.copy.setValue(index, start_date_column, current_start);
            this.copy.setValue(index, end_date_column, current_end);
        }
        
        const view = new google.visualization.DataView(this.copy);
        view.setRows(this.copy.getFilteredRows([
            {column: start_date_column, test: (value, rowId, columnId, datatable) => {return value < target_end;}},
            {column: end_date_column, test: (value, rowId, columnId, datatable) => {return value > target_start;}}
        ]));
        
        document.querySelector("#view_menu .dropdown-menu").classList.remove("show"); // in case it was showing, dismiss it
        timeline.draw(view);
    }
}

const timeline = {
    chart: undefined,
    element: document.getElementById("timeline"),
    selectable: true,
    zoomable: true,
    load() { // this is what starts everything
        google.charts.load("current", {"packages": memory.fullUI ? ["table", "timeline"] : ["timeline"]});
        google.charts.setOnLoadCallback(() => {this.init()});
    },
    init() {
        if (memory.fullUI) {initialize_help();} 
        this.chart = new google.visualization.Timeline(this.element);
        this.when_ready();
        if (memory.fullUI) {
            document.getElementById("whole_menu").classList.remove("invisible");
            google.visualization.events.addListener(this.chart, "select", function() {
                if (timeline.selectable) {
                    if (timeline.zoomable) {
                        menu.view(false); // hide the view menu so as to disallow modifications to the about-to-be-zoomed chart
                        table.zoom();
                    } else {
                        menu.view(true);
                        table.transform();
                    }
                    timeline.zoomable = !timeline.zoomable;
                }
            });
            if (memory.exists()) {table.init(memory.data());}
        }
        else {
            io.url();
        }
    },
    draw(data) {
        document.querySelectorAll(".google-visualization-tooltip").forEach(element => {
            element.remove(); // removes any stray lingering tooltips that didn't disappear off screen automatically
        });
        this.chart.draw(data, memory.options);
    },
    when_ready(chart = this.chart, container = this.element) {
        // correct x-axis text colors on transform, in case dark mode is set at top level
        google.visualization.events.addListener(chart, 'ready', function () {
            timeline.x_axis(container);
        });
    },
    x_axis(container) {
        var color = memory.theme() === "dark" ? "#ffffff" : "#000000";
        container.querySelectorAll("text").forEach(element => {
            if (element.getAttribute('text-anchor') === 'middle') {
                element.setAttribute('fill', color);
            }
        });
    }
}