import apiClient from './api'; 

export const adminService = {
  // ---- MODULE CLASSES (Nouveau) ----
  getClassrooms: async () => {
    // Appel direct vers /admin/classrooms ou /classrooms selon ton routing API Laravel
    const response = await apiClient.get('/admin/classrooms');
    return response.data.data;
  },
  createClassroom: async (classroomData) => {
    const response = await apiClient.post('/admin/classrooms', classroomData);
    return response.data.data;
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
  }
};