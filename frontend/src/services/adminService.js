import apiClient from './api';

// Les routes sont sous /admin/... car la baseURL est déjà http://localhost:8000/api/v1
// et les routes Laravel sont définies sous Route::prefix('v1')->prefix('admin')

export const adminService = {
  // ---- MODULE CLASSES ----
  getClassrooms: async () => {
    const response = await apiClient.get('/admin/classrooms');
    return response.data.data;
  },
  createClassroom: async (classroomData) => {
    const response = await apiClient.post('/admin/classrooms', classroomData);
    return response.data.data;
  },
  updateClassroom: async (id, classroomData) => {
    const response = await apiClient.put(`/admin/classrooms/${id}`, classroomData);
    return response.data.data;
  },
  deleteClassroom: async (id) => {
    const response = await apiClient.delete(`/admin/classrooms/${id}`);
    return response.data;
  },

  // ---- MODULE ÉLÈVES ----
  getStudents: async (filters = {}) => {
    const response = await apiClient.get('/admin/students', { params: filters });
    return response.data.data;
  },
  createStudent: async (studentData) => {
    const response = await apiClient.post('/admin/students', studentData);
    return response.data.data;
  },
  updateStudent: async (id, studentData) => {
    const response = await apiClient.put(`/admin/students/${id}`, studentData);
    return response.data.data;
  },
  transferStudent: async (id, classroomId) => {
    const response = await apiClient.post(`/admin/students/${id}/transfer`, { classroom_id: classroomId });
    return response.data.data;
  },
  expelStudent: async (id) => {
    const response = await apiClient.post(`/admin/students/${id}/expel`);
    return response.data.data;
  },
  deleteStudent: async (id) => {
    const response = await apiClient.delete(`/admin/students/${id}`);
    return response.data;
  },

  // ---- MODULE ENSEIGNANTS ----
  getTeachers: async (filters = {}) => {
    const response = await apiClient.get('/admin/teachers', { params: filters });
    return response.data.data;
  },
  createTeacher: async (teacherData) => {
    const response = await apiClient.post('/admin/teachers', teacherData);
    return response.data.data;
  },
  updateTeacher: async (id, teacherData) => {
    const response = await apiClient.put(`/admin/teachers/${id}`, teacherData);
    return response.data.data;
  },
  deleteTeacher: async (id) => {
    const response = await apiClient.delete(`/admin/teachers/${id}`);
    return response.data;
  },
  assignTeacherClass: async (teacherId, classroomId, subjectId) => {
    const response = await apiClient.post(`/admin/teachers/${teacherId}/assign-classroom`, {
      classroom_id: classroomId,
      subject_id: subjectId
    });
    return response.data.data;
  },
  unassignTeacherClass: async (teacherId, classroomId, subjectId) => {
    const response = await apiClient.post(`/admin/teachers/${teacherId}/unassign-classroom`, {
      classroom_id: classroomId,
      subject_id: subjectId
    });
    return response.data.data;
  },

  // ---- MODULE ABSENCES ----
  getAbsences: async (filters = {}) => {
    const response = await apiClient.get('/admin/absences', { params: filters });
    return response.data.data;
  },
  createAbsence: async (absenceData) => {
    const response = await apiClient.post('/admin/absences', absenceData);
    return response.data.data;
  },
  updateAbsence: async (id, absenceData) => {
    const response = await apiClient.put(`/admin/absences/${id}`, absenceData);
    return response.data.data;
  },
  deleteAbsence: async (id) => {
    const response = await apiClient.delete(`/admin/absences/${id}`);
    return response.data;
  },

  // ---- MODULE SÉQUENCES ----
  getSequences: async (filters = {}) => {
    const response = await apiClient.get('/admin/sequences', { params: filters });
    return response.data.data;
  },
  createSequence: async (sequenceData) => {
    const response = await apiClient.post('/admin/sequences', sequenceData);
    return response.data.data;
  },
  updateSequence: async (id, sequenceData) => {
    const response = await apiClient.put(`/admin/sequences/${id}`, sequenceData);
    return response.data.data;
  },
  deleteSequence: async (id) => {
    const response = await apiClient.delete(`/admin/sequences/${id}`);
    return response.data;
  },

  // ---- MODULE MATIÈRES ----
  getSubjects: async (filters = {}) => {
    const response = await apiClient.get('/admin/subjects', { params: filters });
    return response.data.data;
  },
  createSubject: async (subjectData) => {
    const response = await apiClient.post('/admin/subjects', subjectData);
    return response.data.data;
  },
  updateSubject: async (id, subjectData) => {
    const response = await apiClient.put(`/admin/subjects/${id}`, subjectData);
    return response.data.data;
  },
  deleteSubject: async (id) => {
    const response = await apiClient.delete(`/admin/subjects/${id}`);
    return response.data;
  },

  // ---- MODULE TABLEAUX DE BORD ----
  getDashboardStats: async () => {
    const response = await apiClient.get('/admin/dashboard/stats');
    return response.data.data;
  },
  getAbsenceStats: async () => {
    const response = await apiClient.get('/admin/dashboard/absences-stats');
    return response.data.data;
  },
  getGradeStats: async () => {
    const response = await apiClient.get('/admin/dashboard/grades-stats');
    return response.data.data;
  },

  // ---- MODULE BULLETINS ----
  getBulletins: async (filters = {}) => {
    const response = await apiClient.get('/admin/bulletins', { params: filters });
    return response.data.data;
  },
  downloadStudentBulletin: async (studentId) => {
    const response = await apiClient.get(`/admin/bulletins/student/${studentId}/download`, {
      responseType: 'blob'
    });
    return response.data;
  },
  downloadClassroomBulletin: async (classroomId) => {
    const response = await apiClient.get(`/admin/bulletins/classroom/${classroomId}/download`, {
      responseType: 'blob'
    });
    return response.data;
  },

  // ---- MODULE EMPLOIS DU TEMPS ----
  getTimetables: async (filters = {}) => {
    const response = await apiClient.get('/admin/timetables', { params: filters });
    return response.data.data;
  },
  createTimetable: async (data) => {
    const response = await apiClient.post('/admin/timetables', data);
    return response.data.data;
  },
  updateTimetable: async (id, data) => {
    const response = await apiClient.put(`/admin/timetables/${id}`, data);
    return response.data.data;
  },
  deleteTimetable: async (id) => {
    const response = await apiClient.delete(`/admin/timetables/${id}`);
    return response.data;
  },
};