            </div><!--.vg-main.vg-right-->
        </div><!--.vg-carcass-->
        
        <div class="vg-modal vg-center">
            <?php 
                if(isset($_SESSION['res']['answer'])){
                    echo $_SESSION['res']['answer'];
                    unset($_SESSION['res']);
                }
            ?>
        </div>

                <script>
                    const PATH = '<?=PATH?>';
                    const ADMIN_MODE = 1;
                    const tinyMceDefaulAreas = '<?=implode(',', $this->blocks['vg-content'])?>';
                    console.log(tinyMceDefaulAreas)
                </script>

            <?php $this->getScripts();?>
    </body>
</html>