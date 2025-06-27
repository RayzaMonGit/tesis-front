export const COOKIE_MAX_AGE_1_YEAR = 365 * 24 * 60 * 60

export const PERMISOS = [
    {
      name: 'Dashboard',
      permisos: [
        { name: 'Gráficos', permiso: 'show_report_grafics' },
      ]
    },
    {
      name: 'Roles',
      permisos: [
        { name: 'Registrar', permiso: 'register_rol' },
        { name: 'Listado', permiso: 'list_rol' },
        { name: 'Edición', permiso: 'edit_rol' },
        { name: 'Eliminar', permiso: 'delete_rol' },
      ]
    },
    {
      name: 'Instituciones',
      permisos: [
        { name: 'Registrar', permiso: 'register_institution' },
        { name: 'Listado', permiso: 'list_institution' },
        { name: 'Edición', permiso: 'edit_institution' },
        { name: 'Eliminar', permiso: 'delete_institution' },
        { name: 'Perfil', permiso: 'profile_institution' },
      ]
    },
    {
      name: 'Postulantes',
      permisos: [
        { name: 'Registrar', permiso: 'register_postulant' },
        { name: 'Listado', permiso: 'list_postulant' },
        { name: 'Edición', permiso: 'edit_postulant' },
        { name: 'Eliminar', permiso: 'delete_postulant' },
        { name: 'Perfil', permiso: 'profile_postulant' },
      ]
    },
    {
      name: 'Comisión',
      permisos: [
        { name: 'Registrar', permiso: 'register_comission' },
        { name: 'Listado', permiso: 'list_comission' },
        { name: 'Edición', permiso: 'edit_comission' },
        { name: 'Eliminar', permiso: 'delete_comission' },
      ]
    },
    {
      name: 'Documentos',
      permisos: [
        { name: 'Registrar', permiso: 'register_documents' },
        { name: 'Listado', permiso: 'list_documents' },
        { name: 'Edición', permiso: 'edit_documents' },
        { name: 'Eliminar', permiso: 'delete_documents' },
      ]
    },
    {
      name: 'Convocatorias',
      permisos: [
        { name: 'Registrar', permiso: 'register_convocatories' },
        { name: 'Listado', permiso: 'list_convocatories' },
        { name: 'Edición', permiso: 'edit_convocatories' },
        { name: 'Eliminar', permiso: 'delete_convocatories' },
        { name: 'Listado para postulantes', permiso:'convocatoria_para_postulantes'}
      ]
    },
    {
      name: 'Evaluaciones',
      permisos: [
        { name: 'Registrar', permiso: 'register_evaluation' },
        { name: 'Listado', permiso: 'list_evaluation' },
        { name: 'Edición', permiso: 'edit_evaluation' },
        { name: 'Eliminar', permiso: 'delete_evaluation' },
        { name: 'Asignar a postulante', permiso: 'assign_evaluation_to_postulant' },
      ]
    },
    {
      name: 'Calendario',
      permisos: [
        { name: 'Disponibilidad', permiso: 'calendar' },
      ]
    },
    {
      name: 'Formulario de evaluación',
      permisos: [
        { name: 'Listar', permiso: 'listar_fomulario_evaluacion' },
        { name: 'Crear', permiso: 'crear_formulario_evaluacion' },
        { name: 'Edición', permiso: 'editar_formulario_evaluacion' },
        { name: 'Eliminación', permiso: 'eliminar_formulario_evaluacion' },
        
      ]
    },
  ]
  