<aside class="offcanvas offcanvas-end" id="help" tabindex="-1">
    <header class="offcanvas-header">
        <h5 class="offcanvas-title">Time Line Help</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </header>
    <section class="offcanvas-body">
        <header></header>
        <dl class="mt-2 row">
            <dt class="col-sm-3">Create your data</dt>
            <dd class="col-sm-9">
                <ul>
                    <li>Using Microsoft Excel, Apple Numbers, Google Sheets, or your favorite spreadsheet software, create a table with 4 columns (heading text doesn't matter, as long as it's something):
                        <ol>
                            <li>Category</li>
                            <li>Item</li>
                            <li>Start</li>
                            <li>End&nbsp;<em>(Note: End date values may be blank.)</em></li>
                        </ol>
                        <table id="example_table" class="table table-sm table-bordered table-light">
                            <caption>Example (see timeline at top)</caption>
                            <thead class="table-secondary">
                                <tr>
                                    <th>Category</th>
                                    <th>Item</th>
                                    <th>Start</th>
                                    <th>End</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </li>
                    <li>Save or Export your spreadsheet as a CSV (Comma-separated values) file. <a href="#" onclick="document.getElementById('csv').click();">Upload it.</a></li>
                </ul>
            </dd>
            <dt class="col-sm-3">More examples</dt>
            <dd class="col-sm-9">See more Time Line <a href="examples" target="_blank">examples</a>.</dd>
            <dt class="col-sm-3">What can go wrong?</dt>
            <dd class="col-sm-9">There is minimal error checking. Some things that can cause errors:
                <ul>
                    <li>Attempting to upload a file in a format other than CSV.</li>
                    <li>Arranging columns in a different order than illustrated above.</li>
                    <li>Omitting a Start date value.</li>
                    <li>Providing invalid date formats. Most widely-recognized formats should be OK. For example:
                        <ul>
                            <li>2023-02-25</li>
                            <li>Nov 22, 1963</li>
                            <li>4/15/2023</li>
                        </ul>
                    </li>
                    <li>Using commas in the Category or Item values.</li>
                </ul>
            </dd>
        </dl>
    </section>
</aside>

<script>
    function initialize_help() {
        var input = <?= csv_to_json("examples/example.csv"); ?>;
        const heading = input.shift(); // save off heading row
        input.forEach(row => {
            var tr = document.createElement("TR");
            var td = document.createElement("TD");
            td.innerText = row[0];
            tr.append(td);
            var td = document.createElement("TD");
            td.innerText = row[1];
            tr.append(td);
            var td = document.createElement("TD");
            var d = new Date(row[2]);
            td.innerText = d.toLocaleDateString("en-US", {year: "numeric", month: "short", day: "numeric"});
            tr.append(td);
            var td = document.createElement("TD");
            if (row[3]) {
                var d = new Date(row[3]);
                td.innerText = d.toLocaleDateString("en-US", {year: "numeric", month: "short", day: "numeric"});
            }
            tr.append(td);
            document.querySelector("#example_table tbody").append(tr);
        });
        input.unshift(heading); // restore heading row
        
        const data = table.convert(input);

        const chart = new google.visualization.Timeline(document.querySelector(".offcanvas-body header"));
        timeline.when_ready(chart, document.querySelector(".offcanvas-body header"));
        chart.draw(data, {enableInteractivity: false});
    }
</script>
