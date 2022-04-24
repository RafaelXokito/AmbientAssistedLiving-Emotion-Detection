package pt.ipleiria.estg.dei.ei.pi.AALBackend.ws;


import javax.annotation.security.RolesAllowed;
import javax.ejb.EJB;
import javax.ws.rs.*;
import javax.ws.rs.core.*;

import org.apache.commons.io.IOUtils;
import org.jboss.resteasy.plugins.providers.multipart.InputPart;
import org.jboss.resteasy.plugins.providers.multipart.MultipartFormDataInput;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.exceptions.*;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.ClientDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.FrameDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.dtos.IterationDTO;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.FrameBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.IterationBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.ejbs.PersonBean;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Client;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Frame;
import pt.ipleiria.estg.dei.ei.pi.AALBackend.entities.Iteration;

import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.text.ParseException;
import java.util.List;
import java.util.Map;
import java.util.stream.Collectors;

@Path("frames")
@RolesAllowed({"Client", "Administrator"})
public class FrameService {
    @EJB
    private FrameBean frameBean;
    @EJB
    private IterationBean iterationBean;
    @EJB
    private PersonBean personBean;

    @Context
    private SecurityContext securityContext;

    @POST
    @Path("upload")
    @RolesAllowed({"Client"})
    @Consumes(MediaType.MULTIPART_FORM_DATA)
    @Produces(MediaType.APPLICATION_JSON)
    public IterationDTO upload(MultipartFormDataInput input, @HeaderParam("Authorization") String auth) throws Exception {
        Map<String, List<InputPart>> uploadForm = input.getFormDataMap();
        
        // Get file data to save
        String clientEmail = personBean.getPersonByAuthToken(auth).getEmail();
        String macAddress = uploadForm.get("macAddress").get(0).getBodyAsString();
        Iteration iteration = iterationBean.findIteration(iterationBean.create(macAddress, clientEmail));
        if(iteration == null)
            throw new MyEntityNotFoundException("Iteration could not be created.");

        List<InputPart> inputParts = uploadForm.get("file");
        for (InputPart inputPart : inputParts) {
            try {
                MultivaluedMap<String, String> header = inputPart.getHeaders(); 

                String filename = getFilename(header);
                // convert the uploaded file to inputstream
                InputStream inputStream = inputPart.getBody(InputStream.class, null);
                byte[] bytes = IOUtils.toByteArray(inputStream);
                
                String path = System.getProperty("user.home") + File.separator + "uploads" + File.separator + iteration.getId(); 
                File customDir = new File(path);
                if (!customDir.exists()) {
                    customDir.mkdir();
                }

                String filepath =  customDir.getCanonicalPath() + File.separator + File.separator + filename;
                
                writeFile(bytes, filepath);
                if (!customDir.exists())
                    continue;
                frameBean.create(filename, path, iteration.getId());
            } catch (Exception e) {
                e.printStackTrace();
            }
        }
        System.out.println("FrameService - Upload"+iteration.getFrames().size());
        return iterationToDTO(iterationBean.findIteration(iteration.getId()));
    }

    @GET
    @Path("download/{id}")
    @Produces(MediaType.APPLICATION_OCTET_STREAM)
    public Response download(@PathParam("id") long id, @HeaderParam("Authorization") String auth) throws Exception {
        Frame frame = frameBean.findFrame(id);

        if (!securityContext.isUserInRole("Administrator") && frame.getIteration().getClient().getId() != personBean.getPersonByAuthToken(auth).getId())
            throw new MyUnauthorizedException("You are not allowed to see this frame");

        File fileDownload = new File(frame.getPath() + File.separator + frame.getName());
        Response.ResponseBuilder response = Response.ok(fileDownload);
        response.header("Content-Disposition", "attachment;filename=" + frame.getName());
        return response.build();
    }

    @DELETE
    @Path("delete/{id}")
    @Produces(MediaType.APPLICATION_OCTET_STREAM)
    public Response delete(@PathParam("id") long id, @HeaderParam("Authorization") String auth) throws Exception {
        Frame frame = frameBean.findFrame(id);

        if (!securityContext.isUserInRole("Administrator") && frame.getIteration().getClient().getId() != personBean.getPersonByAuthToken(auth).getId())
            throw new MyUnauthorizedException("You are not allowed to view this frame");

        File fileToDelete = new File(frame.getPath() + File.separator + frame.getName());
        if (fileToDelete.delete()) {
            if (!frameBean.delete(frame))
                return Response.status(400,"File deleted, with errors").build();

            return Response.ok("File deleted").build();
        }

        return Response.status(400, "File was not deleted").build();
    }

    @GET
    @Path("/iteration/{id}")
    @Produces(MediaType.APPLICATION_JSON)
    public List<FrameDTO> getFramesByIteration(@PathParam("id") long id, @HeaderParam("Authorization") String auth) throws Exception {
        Iteration iteration = iterationBean.findIteration(id);
        
        if (!securityContext.isUserInRole("Administrator") && iteration.getClient().getId() != personBean.getPersonByAuthToken(auth).getId())
            throw new MyUnauthorizedException("You are not allowed to view this frame");

        return framesToDTOs(frameBean.getIterationFrames(iteration));
    }

    @GET
    @Path("{id}/exists")
    public Response hasFrames(@PathParam("id") long id, @HeaderParam("Authorization") String auth) throws Exception {
        Iteration iteration = iterationBean.findIteration(id);
        
        if (!securityContext.isUserInRole("Administrator") && iteration.getClient().getId() != personBean.getPersonByAuthToken(auth).getId())
            throw new MyUnauthorizedException("You are not allowed to view this frame");

        return Response.status(Response.Status.OK).entity(!iteration.getFrames().isEmpty()).build();
    }

    FrameDTO toDTO(Frame frame) { return new FrameDTO(
            frame.getId(),
            frame.getName(),
            frame.getPath());
    }

    List<FrameDTO> framesToDTOs(List<Frame> frames) {
        return frames.stream().map(this::toDTO).collect(Collectors.toList());
    }


    private IterationDTO iterationToDTO(Iteration iteration) {
        System.out.println(framesToDTOs(iteration.getFrames()));
        return new IterationDTO(
            iteration.getId(),
            iteration.getMacAddress(),
            clientToDTO(iteration.getClient()),
            framesToDTOs(iteration.getFrames())
        );
    }


    private ClientDTO clientToDTO(Client client) {
        return new ClientDTO(
            client.getEmail(),
            client.getName(),
            client.getAge(),
            client.getContact()
        );
    }

    private String getFilename(MultivaluedMap<String, String> header) {
        String[] contentDisposition = header.getFirst("Content-Disposition").split(";"); for (String filename : contentDisposition) {
            if ((filename.trim().startsWith("filename"))) {
                String[] name = filename.split("=");
                String finalFileName = name[1].trim().replaceAll("\"", ""); return finalFileName;
            } }
        return "unknown";
    }

    private void writeFile(byte[] content, String filename) throws IOException {
        File file = new File(filename);
        if (!file.exists()) {
            file.createNewFile();
        }
        FileOutputStream fop = new FileOutputStream(file);
        fop.write(content);
        fop.flush();
        fop.close();
    }
}
