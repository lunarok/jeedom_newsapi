<?php

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'newsapi');
$eqLogics = eqLogic::byType('newsapi');

?>

<div class="row row-overflow">
  <div class="col-lg-2 col-sm-3 col-sm-4">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fas fa-plus-circle"></i> {{Ajouter un équipement}}</a>
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
        foreach ($eqLogics as $eqLogic) {
          echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
        }
        ?>
      </ul>
    </div>
  </div>

  <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend><i class="fas fa-home"></i> {{Mes Nouvelles}}</legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
          <center>
              <i class="fas fa-plus-circle" style="font-size : 7em;color:#00979c;"></i>
          </center>
          <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>{{Ajouter}}</center></span>
      </div>
      <?php
      foreach ($eqLogics as $eqLogic) {
        $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
        echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
        echo "<center>";
        echo '<img src="plugins/newsapi/plugin_info/newsapi_icon.png" height="105" width="95" />';
        echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
        echo '</div>';
      }
      ?>
    </div>
  </div>

  <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
    <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
    <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
    <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
      <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer"></i> {{Equipement}}</a></li>
      <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
    </ul>
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <form class="form-horizontal">
          <fieldset>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom de la nouvelle}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement newsapi}}"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" >{{Objet parent}}</label>
              <div class="col-sm-3">
                <select class="form-control eqLogicAttr" data-l1key="object_id">
                  <option value="">{{Aucun}}</option>
                  <?php
                  foreach (object::all() as $object) {
                    echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Catégorie}}</label>
              <div class="col-sm-8">
                <?php
                foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                  echo '<label class="checkbox-inline">';
                  echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                  echo '</label>';
                }
                ?>

              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" ></label>
              <div class="col-sm-8">
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label"><a href='https://newsapi.org/register' target="_blank">{{Clef API}}</a></label>
              <div class="col-sm-8">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="api" placeholder="{{Clef API}}"/>
              </div>
            </div>

            <div class="form-group">
							<label class="col-sm-3 control-label">{{Type}}</label>
							<div class="col-sm-8">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type" id="type">
									<option value="top-headlines" selected>Gros Titres</option>
									<option value="everything">Toutes News</option>
								</select>
							</div>
						</div>

            <div class="form-group" id="sortBy">
              <label class="col-sm-3 control-label">{{Numéro de la News à récupérer}}</label>
              <div class="col-sm-8">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="number" placeholder="{{Exemple : 1 pour la plus récente, 2 pour la seconde ...}}"/>
              </div>
            </div>

            <div class="form-group" id="country">
              <label class="col-sm-3 control-label">{{Pays}}</label>
              <div class="col-sm-8">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="country" placeholder="{{Le code international à 2 lettres du Pays}}"/>
              </div>
            </div>

            <div class="form-group" id="language">
              <label class="col-sm-3 control-label">{{Langue}}</label>
              <div class="col-sm-8">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="languages" placeholder="{{Le code international à 2 lettres du Pays}}"/>
              </div>
            </div>

            <div class="form-group" id="category">
              <label class="col-sm-3 control-label">{{Catégorie}}</label>
              <div class="col-sm-8">
                <select class="form-control eqLogicAttr" data-l1key="configuration" data-l2key="category">
                  <option value="none">{{Aucune}}</option>
                  <option value="business">{{Business}}</option>
                  <option value="entertainment">{{Divertissement}}</option>
                  <option value="general">{{Générale}}</option>
                  <option value="health">{{Santé}}</option>
                  <option value="science">{{Science}}</option>
                  <option value="sports">{{Sports}}</option>
                  <option value="technology">{{Technologie}}</option>
                </select>
              </div>
            </div>

            <div class="form-group" id="sources">
              <label class="col-sm-3 control-label">{{Sources}}</label>
              <div class="col-sm-8">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="sources" placeholder="{{Voir la liste des Sources dans la doc}}"/>
              </div>
            </div>

            <div class="form-group" id="keyword">
              <label class="col-sm-3 control-label">{{Mot Clef}}</label>
              <div class="col-sm-8">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="q" placeholder="{{Exemple : domotique}}"/>
              </div>
            </div>

            <div class="form-group" id="domains">
              <label class="col-sm-3 control-label">{{Domaines}}</label>
              <div class="col-sm-8">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="domains" placeholder="{{Exemple : techcrunch.com}}"/>
              </div>
            </div>

            <div class="form-group" id="excludeDomains">
              <label class="col-sm-3 control-label">{{Domaines Exclus}}</label>
              <div class="col-sm-8">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="excludeDomains" placeholder="{{Exemple : techcrunch.com}}"/>
              </div>
            </div>

            <div class="form-group" id="sortBy">
              <label class="col-sm-3 control-label">{{Classement Par}}</label>
              <div class="col-sm-8">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="sortBy" placeholder="{{Exemple : relevancy, popularity, publishedAt}}"/>
              </div>
            </div>

          </fieldset>
        </form>
      </div>

      <div role="tabpanel" class="tab-pane" id="commandtab">

        <table id="table_cmd" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th style="width: 50px;">#</th>
              <th style="width: 150px;">{{Nom}}</th>
              <th style="width: 110px;">{{Sous-Type}}</th>
              <th style="width: 100px;">{{Valeur}}</th>
              <th style="width: 200px;">{{Paramètres}}</th>
              <th style="width: 100px;"></th>
            </tr>
          </thead>
          <tbody>

          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>

<?php include_file('desktop', 'newsapi', 'js', 'newsapi'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
