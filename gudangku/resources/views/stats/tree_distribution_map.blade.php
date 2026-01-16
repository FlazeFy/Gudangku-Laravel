<style>
    #inventory-tree_map svg {
        min-height: 70vh;
    }
</style>
<!-- ApexTree -->
<script src="https://cdn.jsdelivr.net/npm/apextree"></script>

<div id="inventory-tree_map"></div>

<script>
    const get_inventory_tree_map = () => {
        Swal.showLoading()
        const title = 'My Inventory'
        const ctx = 'inventory-tree_map_temp'
        const ctx_holder = "inventory-tree_map"

        const failedMsg = () => {
            Swal.fire({
                title: "Oops!",
                text: `Failed to get the stats Total ${title}`,
                icon: "error"
            });
        }
        const fetchData = () => {
            $.ajax({
                url: `/api/v1/stats/inventory/tree_map`,
                type: 'GET',
                beforeSend: function (xhr) {
                    xhr.setRequestHeader("Accept", "application/json")
                    xhr.setRequestHeader("Authorization", `Bearer ${token}`)    
                },
                success: function(response) {
                    Swal.close()
                    const data = response.data
                    localStorage.setItem(ctx,JSON.stringify(data))
                    localStorage.setItem(`last-hit-${ctx}`,Date.now())
                    generate_tree_map(title,ctx_holder,data)
                },
                error: function(response, jqXHR, textStatus, errorThrown) {
                    Swal.close()
                    if(response.status != 404){
                        failedMsg()
                    } else {
                        templateAlertContainer(ctx_holder, 'no-data', "No inventory found for this context to generate the stats", 'add a inventory', '<i class="fa-solid fa-warehouse"></i>','/inventory/add')
                        $(`#${ctx_holder}`).prepend(`<h2 class='title-chart'>${ucEachWord(title)}</h2>`)
                    }
                }
            });
        }

        const generate_tree_map = (title,ctx_holder,raw_data) => {
            const data = {
                id: title,
                name: title,
                children: raw_data,
            };

            const options = {
                contentKey: 'name',
                // width: 800,
                height: 700,
                nodeWidth: 90,
                nodeHeight: 40,
                nodeBGColor: 'var(--darkColor)',
                nodeBGColorHover: 'var(--primaryColor)',
                edgeColor: 'var(--greyColor)',
                edgeColorHover: 'var(--whiteColor)',
                borderRadius: 'var(--roundedLG)',
                childrenSpacing: 150,
                siblingSpacing: 30,
                nodeWidth: 220,   
                nodeHeight: 70,  
                childrenSpacing: 100,
                siblingSpacing: 15,
                padding: 'var(--spaceSM)', 
                direction: 'top',
                fontSize: 'var(--textXMD)',
                fontFamily: 'sans-serif',
                fontWeight: '500',
                fontColor: 'var(--whiteColor)',
                borderWidth: 2,
                borderColor: 'var(--primaryColor)',
                borderColorHover: 'var(--whiteColor)',
                canvasStyle: 'border:calc(var(--spaceMini)/2) solid var(--whiteColor); background: transparent; border-radius:var(--roundedLG);',
            };

            const tree = new ApexTree(document.getElementById(ctx_holder), options);
            const graph = tree.render(data);
            graph.changeLayout('left')

            $(document).ready(function() {
                document.getElementById('layoutTop').addEventListener('click', (e) => {
                    graph.changeLayout('top');
                });
                document.getElementById('layoutBottom').addEventListener('click', (e) => {
                    graph.changeLayout('bottom');
                });

                document.getElementById('layoutLeft').addEventListener('click', (e) => {
                    graph.changeLayout('left');
                });
                document.getElementById('layoutRight').addEventListener('click', (e) => {
                    graph.changeLayout('right');
                });

                document.getElementById('fitScreen').addEventListener('click', (e) => {
                    graph.fitScreen();
                });
            })
        }

        if(ctx in localStorage){
            const lastHit = parseInt(localStorage.getItem(`last-hit-${ctx}`))
            const now = Date.now()

            if(((now - lastHit) / 1000) < statsFetchRestTime){
                const data = JSON.parse(localStorage.getItem(ctx))
                if(data){
                    generate_tree_map(title,ctx_holder,data)
                    Swal.close()
                } else {
                    Swal.close()
                    failedMsg()
                }
            } else {
                fetchData()
            }
        } else {
            fetchData()
        }
    }
    get_inventory_tree_map()

    
</script>