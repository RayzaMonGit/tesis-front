export default [
  {
    title: 'Dashboard',
    to: { name: 'dashboard' },
    icon: { icon: 'ri-bar-chart-2-line' },
    permission: 'all',
  },
  {
    heading: 'Accesos', permissions: ['list_rol', "register_rol",
      "edit_rol",
      "delete_rol"]
  },
  {
    title: 'Usuarios',
    to: { name: 'staffs' },
    permissions: ['list_rol', "register_rol",
      "edit_rol",
      "delete_rol"],
    icon: { icon: 'ri-user-3-line' },
  },
  {
    title: 'Roles y Permisos',
    to: { name: 'roles-permisos' },
    icon: { icon: 'ri-lock-password-line' },
    permissions: ['list_rol', "register_rol",
      "edit_rol",
      "delete_rol"],
  },

  {
    heading: 'Gestión', permissions: ["register_postulant",
      "list_postulant"
      , "edit_postulant"
      , "delete_postulant"
      //,"profile_postulant"
      , "register_comission"
      , "list_comission"
      , "edit_comission"
      , "delete_comission"
    ]
  },

  {
    title: 'Postulantes',
    icon: { icon: 'ri-file-user-line' },
    children: [
      {
        title: 'Registrar',
        to: { name: 'postulantes-add' },
        icon: { icon: 'ri-user-add-line' },
        permission: 'register_postulant',
      },
      {
        title: 'Listado',
        to: { name: 'postulantes-list' },
        icon: { icon: 'ri-list-unordered' },
        permissions: ["list_postulant"
          , "edit_postulant"
          , "delete_postulant"],
      },
    ],
  },
  {
    title: 'Comisiones',
    icon: { icon: 'ri-team-line' },
    children: [
      {
        title: 'Asignaciones',
        to: { name: 'comisiones-crear' },
        icon: { icon: 'ri-user-add-line' },
        permission: 'register_comission',
      },
      /*{
        title: 'Ver Comisiones',
        to: { name: 'comisiones-listado' },
        icon: { icon: 'ri-group-line' },
        permissions:["list_comission"
,"edit_comission"
,"delete_comission"],
      },*/
    ],
  },
  {
    heading: 'Evaluaciones', permissions: [
      "register_evaluation"
      , "list_evaluation"
      , "edit_evaluation"
      , "delete_evaluation"
      , "assign_evaluation_to_postulant",
      'crear_formulario_evaluacion',
      'editar_formulario_evaluacion',
      'eliminar_formulario_evaluacion',
      'listar_fomulario_evaluacion'
      , "register_convocatories"
      , "list_convocatories"
      , "edit_convocatories"
      , "delete_convocatories",
      "convocatoria_para_postulantes",
    ]
  },
  {
    title: 'Convocatorias',
    icon: { icon: 'ri-megaphone-line' },
    children: [
      {
        title: 'Crear Convocatoria',
        to: { name: 'convocatorias-add' },
        icon: { icon: 'ri-add-circle-line' },
        permissions: ["register_convocatories"
          , "edit_convocatories"
          , "delete_convocatories"],
      },
      {
        title: 'Ver Convocatorias',
        to: { name: 'convocatorias-list' },
        icon: { icon: 'ri-calendar-event-line' },
        permission: 'list_convocatories',
      },
      {
        title: 'Convocatorias Vigentes',
        to: { name: 'convocatorias-vistapostul' },
        icon: { icon: 'ri-calendar-event-line' },
        permission: 'convocatoria_para_postulantes',

      },
      

    ],
  },
   {
title: 'Mis Postulaciones',
    to: { name: 'postulaciones-mispostul' },
    icon: { icon: 'ri-article-line' },
    permissions: ['convocatoria_para_postulantes', 'list_documentos_postulante'],
   },
  {
    title: 'Formulario de evaluación',
    icon: { icon: 'ri-clipboard-line' },
    children: [
      {
        title: 'Crear formulario',
        to: { name: 'formularios-add' },
        icon: { icon: 'ri-clipboard-fill' },
        permissions: ['crear_formulario_evaluacion',
          'editar_formulario_evaluacion',
          'eliminar_formulario_evaluacion',
          'listar_fomulario_evaluacion',
        ],
      },
      {
        title: 'Ver formularios',
        to: { name: 'formularios-list' },
        icon: { icon: 'ri-clipboard-fill' },
        permissions: ['crear_formulario_evaluacion',
          'editar_formulario_evaluacion',
          'eliminar_formulario_evaluacion',
          'listar_fomulario_evaluacion',
        ],
      },
    ],
  },
  {
    title: 'Evaluaciones',
    icon: { icon: 'ri-clipboard-line' },
    children: [
      {
        title: 'Registrar Evaluación',
        to: { name: 'evaluaciones-registrar' },
        icon: { icon: 'ri-clipboard-fill' },
        permissions: ["register_evaluation"
          , "edit_evaluation"
          , "delete_evaluation"
          , "assign_evaluation_to_postulant"],
      },
      {
        title: 'Listado',
        to: { name: 'evaluaciones-list' },
        icon: { icon: 'ri-list-check' },
        permission: 'list_evaluation',
      },
    ],
  },





  /* {
     title: 'Instituciones',
     icon: { icon: 'ri-building-line' },
     children: [
       {
         title: 'Registrar',
         to: { name: 'instituciones-registrar' },
         icon: { icon: 'ri-add-line' },
         permissions:[] 'register_institution',
       },
       {
         title: 'Listado',
         to: { name: 'instituciones-listado' },
         icon: { icon: 'ri-bank-line' },
         permissions:[] 'list_institution',
       },
     ],
   },
 
   { heading: 'Calendario y Configuración', permissions: ['calendar'] },
   {
     title: 'Calendario',
     to: { name: 'calendario' },
     icon: { icon: 'ri-calendar-line' },
     permissions:[] 'calendar',
   },*/
  {
    heading: 'Documentacion', permissions: [
      "register_documents"
      , "list_documents"
      , "edit_documents"
      , "delete_documents"

    ]
  },

  
    
      /*{
        title: 'Subir Documento',
        to: { name: 'documentos-subir' },
        icon: { icon: 'ri-upload-cloud-line' },
        permissions: ["register_documents",
          , "edit_documents"
          , "delete_documents"],
      },*/
      {
        title: 'Auditorias Convocatorias',
       to: { name: 'convocatorias-historialauditoria' },
        icon: { icon: 'ri-folder-line' },
        permission: 'list_documents',
      },
    
  
  {
    heading: 'Configuración', permissions: [
      "register_rol"
      , "list_rol"
      , "edit_rol"
      , "delete_rol"
    ]
  },
  {
    title: 'Módulos y Operaciones',
    to: { name: 'modulos-operaciones' },
    icon: { icon: 'ri-settings-3-line' },
    permissions: ["register_rol"
      , "list_rol"
      , "edit_rol"
      , "delete_rol"],
  },
];
