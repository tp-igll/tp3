<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\EtudiantRequest;
use App\Etudiant;
use Symfony\Component\HttpFoundation\Response;


class EtudiantController extends Controller
{
    
    

    /**
     * La fonction matricule génère un matricule pour un étudiant lors de l'inscrpition
     *
     * @return $matricule
     */
    public function matricule(){
        $rang=Etudiant::count()+1;
        if (($rang>=1) && ($rang<10)) $matricule=date('y').'/000'.$rang;
        else if (($rang>=10) && ($rang<99)) $matricule=date('y').'/00'.$rang;
        else $matricule=date('y').'/0'.$rang;
        return $matricule;
    }

    /**
     * La fonction create_email crée un email pour un étudiant lors de l'inscription 
     * @param $nom le nom de l'étudiant
     * @param $prenom prénom de léetdjkhjh
     *@return $email
     */

    public function create_email($nom,$prenom) {
        $date=date('y');
        $rang = ((int) $date) - 9+96 ;
        return chr($rang).$prenom[0].'_'.$nom.'@esi.dz';
    }

    /**
     * La fonction generer_section génère une section pour un étudiant d'une façon aléatoire et selon son niveau
     *
     * @param  $niv
     *
     * @return $section
     */
    public function generer_section($niv){
        switch ($niv) {
            case "1CS":{
                return chr(mt_rand(1,2)+64); // 1CS A Ou B
            break;
            }
            case "2CS": {
                $specialite=['SIQ','SIT','SIL']; //2cs SIQ,SIL Ou SIT
                return $specialite[mt_rand(0,2)];
            break;
            }
            case "3CS" :{
            break; //3CS N'ont pas de section
            }
            default : {
                return chr(mt_rand(1,3)+64);//1,2CP A,B OU C
            break;
            }
        }
    }
    /**
     *La fonction generer_groupe génère un groupe a un étudiant suivant son niveau et sa section et selon une manière aléatoire
     *
     * @param  $niv
     * @param  $sect
     *
     * @return $groupe
     */
    public function generer_groupe($niv,$sect){
        switch ($niv) {
            case "1CS": {
                if ($sect=='A') return mt_rand(1,4);
                else return mt_rand(1,4)+4;
            break;
            }
            case "2CS":  {
                return mt_rand(1,2);
            break;
            }
            case "3CS": {break;}
            default : {
                if ($sect=='A') return mt_rand(1,3);
                else if ($sect=='B') return mt_rand(1,3)+3;
                else if ($sect=='C') return mt_rand(1,3)+6;
            break;
            }
        }

    }
    /**
     *La fonction generer_niveau génère un niveau a un étudiant d'une manière aléatoire
     *
     * @return $niveau
     */
    public function generer_niveau(){
        $niv=['1CP','2CP','1CS','2CS','3CS'];
        return $niv[mt_rand(0,4)];
    }
    /************** CRUD FUNCTIONS***************************/
    /**
     * La fonction index récupère tous les étudiants dans un tableau 
     *
     * @return $etudiants
     */
    public function index() { //Récupère tous les étudiants dans un tableau $etudiants
        $etudiants = Etudiant::all(['nom','prenom','grp','email','sect','niv','matricule'])->toArray();
        $etudiants_tout=Etudiant::all(['nom','prenom','grp','email','sect','niv','matricule','date_naissance','adresse','numero'])->toArray();
        return array('consultation'=>$etudiants,'form'=>$etudiants_tout);
    }

    public function create() {
        return view('app');
    }

    /**
     * La fonction store inscrit un étudiant dans la base de données
     *
     * @param  $request
     *
     * 
     */
    public function store(EtudiantRequest $request) { //INSCRIT UN ETUDIANT DANS LA BDD
        $infos=$request->input()['0'];
        if (!(Etudiant::where('numero',$infos['num'])->first())) {
            $etudiant = new Etudiant();
            $etudiant->nom=$infos['nom'];
            $etudiant->prenom=$infos['prenom'];
            $etudiant->email=$this->create_email($infos['nom'],$infos['prenom']);
            $etudiant->matricule=$this->matricule();
            $etudiant->date_naissance=date('Y/d/m',strtotime($infos['date_naissance']));
            $etudiant->adresse=$infos['adresse'];
            $etudiant->numero=$infos['num'];
            $etudiant->niv=$this->generer_niveau();
            $etudiant->sect=$this->generer_section($etudiant->niv);
            $etudiant->grp=$this->generer_groupe($etudiant->niv,$etudiant->sect);
            $etudiant->save();
            return response(null, Response::HTTP_CREATED);
        }
        else return response(null, Response::HTTP_IM_USED); //Existe déjà
    }


    /**
     * La fonction update permet de modifier les données d'un étudiant
     *
     * @param  $numero
     * @param  $request
     *
     * 
     */
    public function update($numero, EtudiantRequest $request) { //Modifie étudiant dont l'id = $id
        $id=Etudiant::where('numero',$numero)->value('id');
        $etudiant = Etudiant::find($id);
        $etudiant->update($request->all());
        return response(null, Response::HTTP_MODIFIED);
    }


    /**
     * La fonction destroy permet de supprimer un étudiants de la base de données
     *
     * @param   $numero
     *
     * 
     */
    public function destroy ($numero) {//Supprime un étudiant
        $id=Etudiant::where('numero',$numero)->value('id');
        $etudiant = Etudiant::destroy($id);
        return response(null, Response::HTTP_OK);
    }
}

